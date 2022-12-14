<?php

namespace App\Command;

use App\Models\EmailQueue;
use App\Models\Ip;
use App\Models\Node;
use App\Models\User;
use App\Models\Product;
use App\Models\Token;
use App\Models\Bought;
use App\Models\Ticket;
use App\Models\SigninIp;
use App\Models\TrafficLog;
use App\Models\Disconnect;
use App\Models\EmailVerify;
use App\Models\NodeInfoLog;
use App\Models\NodeOnlineLog;
use App\Models\PasswordReset;
use App\Models\TelegramTasks;
use App\Models\TelegramSession;
use App\Models\UserSubscribeLog;
use App\Models\Setting;
use App\Models\Paytake;
use App\Models\Order;
use App\Models\Payback;
use App\Services\Mail;
use App\Services\ZeroConfig;
use App\Utils\Telegram\TelegramTools;
use App\Utils\Tools;
use App\Utils\Telegram;
use App\Utils\DatatablesHelper;
use Swap\Builder;
use ArrayObject;
use Exception;

class Job extends Command
{
    public $description = ''
    . '├─=: php xcat Job [选项]' . PHP_EOL
    . '│ ├─ DailyJob                - 每日任务' . PHP_EOL
    . '│ ├─ CheckJob                - 检查任务，每分钟' . PHP_EOL
    . '│ ├─ CheckUserClassExpire    - 检查用户会员等级过期任务，每分钟' . PHP_EOL
    . '│ ├─ CheckOrderStatus        - 检查订单状态任务，每分钟' . PHP_EOL
    . '│ ├─ CheckUserExpire         - 检查账号过期任务，每小钟' . PHP_EOL
    . '│ ├─ UserJob                 - 用户账户相关任务，每小时' . PHP_EOL
    . '│ ├─ SendMail                - 处理邮件队列' . PHP_EOL;

    public function boot()
    {
        if (count($this->argv) === 2) {
            echo $this->description;
        } else {
            $methodName = $this->argv[2];
            if (method_exists($this, $methodName)) {
                $this->$methodName();
            } else {
                echo '方法不存在.' . PHP_EOL;
            }
        }
    }

    /**
     * 每日任务
     *
     * @return void
     */
    public function DailyJob()
    {
        ini_set('memory_limit', '-1');

        // 重置节点流量
        echo '重置节点流量开始' . PHP_EOL;
        Node::where('bandwidthlimit_resetday', date('d'))->update(['node_bandwidth' => 0]);
        echo '重置节点流量结束;' . PHP_EOL;

        // 清理各表记录
        echo '清理数据库各表开始' . PHP_EOL;
        UserSubscribeLog::where('request_time', '<', date('Y-m-d H:i:s', time() - 86400 * (int)Setting::obtain('subscribe_log_save_days')))->delete();
        Token::where('expire_time', '<', time())->delete();
        EmailVerify::where('expire_in', '<', time() - 86400 * 3)->delete();
        PasswordReset::where('expire_time', '<', time() - 86400 * 3)->delete();
        Ip::where('datetime', '<', time() - 300)->delete();
        TelegramSession::where('datetime', '<', time() - 900)->delete();
        SigninIp::where('datetime', '<', time() - 86400 * 7)->delete();
        IP::where('datetime', '<', time() - 86400 * 7)->delete();
        echo '清理数据库各表结束;' . PHP_EOL;

        // ------- 重置自增 ID
        $db = new DatatablesHelper();

        $tools = new Tools();
        $tools->reset_auto_increment($db, 'user_traffic_log');
        $tools->reset_auto_increment($db, 'node_online_log');
        $tools->reset_auto_increment($db, 'node_info');

        //auto reset
        echo '重置用户流量开始' . PHP_EOL;
        
        $orders = Order::where('order_status', 'paid')->where('order_type', 'purchase_product_order')->get();
        $order_users = array();
        foreach ($orders as $order) {
            
            $user = User::where('id', $order->user_id)->first();
            if ($user == null) {
                continue;
            }
            $product = Product::where('id', $order->product_id)->first();
            if ($product == null) {
                continue;
            }
            if ($product->reset() != 0 && $product->reset_value() != 0 && $product->reset_exp() != 0) {
                $order_users[] = $order->user_id;
                if ($user->class > 0 && (int)((time() - $order->paid_time) / 86400) % $product->reset() == 0 && (int)((time() - $order->paid_time) / 86400) != 0) {
                    echo('用户ID:' . $user->id . ' 根据套餐ID:' . $product->id . ' 重置流量为' . $product->reset_value() . 'GB' . PHP_EOL);
                    $user->transfer_enable = Tools::toGB($product->reset_value());
                    $user->u = 0;
                    $user->d = 0;
                    $user->last_day_t = 0;
                    $user->save();
                    $user->sendMail(
                        Setting::obtain('website_general_name') . '-您的流量被重置了',
                        'news/warn.tpl',
                        [
                            'text' => '您好，根据您所订购的订单 ID:' . $order->no . '，流量已经被重置为' . $product->reset_value() . 'GB'
                        ],
                        [],
                        $_ENV['email_queue']
                    );
                }

            }
        }
    
        User::chunkById(1000, function ($users) use ($order_users) {
            foreach ($users as $user) {
                /** @var User $user */
                $user->last_day_t = ($user->u + $user->d);
                $user->save();
                if (in_array($user->id, $order_users)) {
                    continue;
                }
                if (date('d') == Setting::obtain('user_general_free_user_reset_day')) {
                    $user->u = 0;
                    $user->d = 0;
                    $user->last_day_t = 0;
                    $user->transfer_enable = Setting::obtain('user_general_free_user_reset_traffic') * 1024 * 1024 * 1024;
                    $user->save();
                    $user->sendMail(
                        Setting::obtain('website_general_name') . '-您的免费流量被重置了',
                        'news/warn.tpl',
                        [
                            'text' => '您好，您的免费流量已经被重置为' . $user->auto_reset_bandwidth . 'GB'
                        ],
                        [],
                        $_ENV['email_queue']
                    );
                }
            }
        });
        echo '重置用户流量结束' . PHP_EOL;
         // ------- 发送每日系统运行报告
        if (Setting::obtain('enable_system_clean_database_report_telegram_notify') == true) {
            echo '每日数据库清理成功报告发送开始' . PHP_EOL;
            $sendAdmins = (array)json_decode(Setting::obtain('telegram_general_admin_id'));
            foreach ($sendAdmins as $sendAdmin) {
                $admin_telegram_id = User::where('id', $sendAdmin)->where('is_admin', '1')->value('telegram_id');
                $messagetext = Setting::obtain('diy_system_clean_database_report_telegram_notify_content');
                Telegram::PushToAdmin($messagetext, $admin_telegram_id);
            }
            echo '每日数据库清理成功报告发送结束' . PHP_EOL;
        }

        $this->ZeroTask();


        $configs = Setting::getClass('currency');
        if ($configs['enable_currency'] == true) {
            $swap = (new Builder())
                ->add('abstract_api', ['api_key' => $configs['currency_exchange_rate_api_key']])
            ->build();
            $rate = $swap->latest($configs['setting_currency'] . '/CNY');
            $result = $rate->getValue();
            $setting = Setting::where('item', '=', 'currency_exchange_rate')->first();
            $setting->value = substr($result, 0, 4);
            $setting->save();
        }
        echo 'Success ' . date('Y-m-d H:i:s', time()) . PHP_EOL;
    }

    /**
     * 检查任务，每分钟
     *
     * @return void
     */
    public function CheckJob()
    {

        //节点掉线检测
        if ($_ENV['enable_detect_offline'] == true) {
            echo '节点掉线检测开始' . PHP_EOL;
            $adminUser = User::where('is_admin', '=', '1')->get();
            $nodes = Node::all();
            foreach ($nodes as $node) {
                if ($node->isNodeOnline() === false && $node->online == true) {
                    
					foreach ($adminUser as $user) {
					    if ($_ENV['sendemail'] === true) {
							echo 'Send offline mail to user: ' . $user->id . PHP_EOL;
							$user->sendMail(
								Setting::obtain('website_general_name') . '-系统警告',
								'news/warn.tpl',
								[
									'text' => '管理员您好，系统发现节点 ' . $node->name . ' 掉线了，请您及时处理。'
								],
								[],
								$_ENV['email_queue']
							);
					    }
						$notice_text = str_replace(
							'%node_name%',
							$node->name,
							Setting::obtain('diy_system_node_offline_report_telegram_notify_content')
						);
					}
                    

                    if (Setting::obtain('enable_system_node_offline_report_telegram_notify') == true) {
                        $sendAdmins = (array)json_decode(Setting::obtain('telegram_general_admin_id'));
                        foreach ($sendAdmins as $sendAdmin) {
                            $admin_telegram_id = User::where('id', $sendAdmin)->where('is_admin', '1')->value('telegram_id');
                            $messagetext = $notice_text;
                            Telegram::PushToAdmin($messagetext, $admin_telegram_id);
                        }
                    }

                    $node->online = false;
                    $node->save();
                } elseif ($node->isNodeOnline() === true && $node->online == false) {
                    foreach ($adminUser as $user) {
                        if ($_ENV['sendemail'] === true) {
                            echo 'Send offline mail to user: ' . $user->id . PHP_EOL;
                            $user->sendMail(
                                Setting::obtain('website_general_name') . '-系统提示',
                                'news/warn.tpl',
                                [
                                    'text' => '管理员您好，系统发现节点 ' . $node->name . ' 恢复上线了。'
                                ],
                                [],
                                $_ENV['email_queue']
                            );
                        }
                        $notice_text = str_replace(
                            '%node_name%',
                            $node->name,
                            Setting::obtain('diy_system_node_online_report_telegram_notify_content')
                        );
                    }

                    if (Setting::obtain('enable_system_node_online_report_telegram_notify') == true) {
                        $sendAdmins = (array)json_decode(Setting::obtain('telegram_general_admin_id'));
                        foreach ($sendAdmins as $sendAdmin) {
                            $admin_telegram_id = User::where('id', $sendAdmin)->where('is_admin', '1')->value('telegram_id');
                            $messagetext = $notice_text;
                            Telegram::PushToAdmin($messagetext, $admin_telegram_id);
                        }
                    }

                    $node->online = true;
                    $node->save();
                }
            }
            echo '节点掉线检测结束' . PHP_EOL;
        }

        if (Setting::obtain('enable_telegram_bot') == true) {
            $this->Telegram();
        }

        //更新节点 IP，每分钟
        echo '更新节点IP开始' . PHP_EOL;
        $nodes = Node::get();
        foreach ($nodes as $node) {
            /** @var Node $node */
            $server = $node->getOutAddress();
            if (!Tools::isIPv4($server) && $node->changeNodeIp($server)) {
                $node->save();
            }
        }
        echo '更新节点IP结束' . PHP_EOL;

        echo 'Success ' . date('Y-m-d H:i:s', time()) . PHP_EOL;
    }

    /**
     * Telegram 任务
     */
    public function Telegram(): void
    {
        # 删除 tg 消息
        echo '删除telegram无用消息开始' . PHP_EOL;
        $TelegramTasks = TelegramTasks::where('type', 1)->where('executetime', '<', time())->get();
        foreach ($TelegramTasks as $Task) {
            TelegramTools::SendPost(
                'deleteMessage',
                ['chat_id' => $Task->chatid, 'message_id' => $Task->messageid]
            );
            TelegramTasks::where('chatid', $Task->chatid)->where('type', '<>', 1)->where(
                'messageid',
                $Task->messageid
            )->delete();
            $Task->delete();
        }
        echo '删除telegram无用消息结束' . PHP_EOL;
    }

    public function ZeroTask()
    {
        echo '关闭工单任务开始' . PHP_EOL;
        if (ZeroConfig::get('auto_close_ticket') === true) {
            $tickets = Ticket::where('status', '=', 1)->where('rootid', '=', 0)->get();

            foreach ($tickets as $ticket) {
                $tk = Ticket::where('rootid', '=', $ticket->id)->orderBy('datetime', 'desc')->first();
                $tk_userid = $tk ? $tk->userid : $ticket->userid;

                $user = User::find($tk_userid);
                if ($user === null) {
                    continue;
                }
                if ($user->is_admin != 1) {
                    continue;
                }

                $time = ZeroConfig::get('close_ticket_time') * 86400;
                if (time() - $tk->datetime < $time) {
                    continue;
                }

                $ticket->status = 0;
                $ticket->save();
                echo('关闭工单ID:' . $ticket->id . PHP_EOL);
            }
        }

        if (ZeroConfig::get('del_user_ticket') === true) {
            $del_tickets = Ticket::all();
            foreach ($del_tickets as $del_ticket) {
                $del_user = User::find($del_ticket->userid);
                if ($del_user === null) {
                    $del_ticket->delete();
                }
            }
        }
        echo '关闭工单任务结束' . PHP_EOL;
    }

    /**
     * 用户账户相关任务，每小时
     *
     * @return void
     */
    public function UserJob()
    {
        $users = User::all();
        foreach ($users as $user) {
            if (Setting::obtain('enable_insufficient_traffic_user_notify') != false) {
                echo '用户订阅余量检测开始' . PHP_EOL;
                $user_traffic_left = $user->transfer_enable - $user->u - $user->d;
                $under_limit = false;

                if ($user->transfer_enable != 0 && $user->class != 0) {
                    if (
                        Tools::flowToMB($user_traffic_left) < 1000
                    ) {
                        $under_limit = true;
                        $unit_text = 'MB';
                    }
                }

                if ($under_limit == true && $user->traffic_notified == false) {
                    $result = $user->sendMail(
                        Setting::obtain('website_general_name') . '-您的剩余流量过低',
                        'news/warn.tpl',
                        [
                            'text' => '您好，系统发现您剩余流量已经低于 ' . 1000 . $unit_text . ' 。'
                        ],
                        [],
                        $_ENV['email_queue']
                    );
                    if ($result) {
                        $user->traffic_notified = true;
                        $user->save();
                    }
                } elseif ($under_limit == false && $user->traffic_notified == true) {
                    $user->traffic_notified = false;
                    $user->save();
                }
                echo '用户订阅余量检测结束' . PHP_EOL;
            }
        }
    }

    /**
     * 发邮件
     *
     * @return void
     */
    public function SendMail()
    {
        if (file_exists(BASE_PATH . '/storage/email_queue')) {
            echo "程序正在运行中" . PHP_EOL;
            return false;
        }
        $myfile = fopen(BASE_PATH . '/storage/email_queue', 'wb+') or die('Unable to open file!');
        $txt = '1';
        fwrite($myfile, $txt);
        fclose($myfile);
        // 分块处理，节省内存
        EmailQueue::chunkById(1000, function ($email_queues) {
            foreach ($email_queues as $email_queue) {
                try {
                    Mail::send($email_queue->to_email, $email_queue->subject, $email_queue->template, json_decode($email_queue->array), []);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                echo '发送邮件至 ' . $email_queue->to_email . PHP_EOL;
                $email_queue->delete();
            }
        });
        unlink(BASE_PATH . '/storage/email_queue');
        echo 'Success ' . date('Y-m-d H:i:s', time()) . PHP_EOL;
    }
    
    /**
     * 检查用户等级过期时间
     */
    public function CheckUserClassExpire()
    {
        $configs = Setting::getClass('register');
        echo '用户等级过期检测开始' . PHP_EOL;
        $users = User::query()
            ->where('class_expire', '<', date('Y-m-d H:i:s', time()))
            ->where('class', '!=', 0)
            //->where('is_admin', '!=', 1)
            ->get();

        foreach ($users as $user) {
            $text = '您好，系统发现您的账号等级已经过期了。';
            $reset_traffic = Setting::obtain('user_general_class_expire_reset_traffic');
            if ($reset_traffic >= 0) {
                $user->transfer_enable = Tools::toGB($reset_traffic);
                $user->u = 0;
                $user->d = 0;
                $user->last_day_t = 0;
                $text .= '流量已经被重置为' . $reset_traffic . 'GB';
            }
            $user->sendMail(
                Setting::obtain('website_general_name') . '-您的账户等级已经过期了',
                'news/warn.tpl',
                [
                    'text' => $text
                ],
                [],
                $_ENV['email_queue']
            );
            $user->class = 0;
            $user->node_connector = $configs['connection_device_limit'];
            $user->node_speedlimit = $configs['connection_rate_limit'];
            $user->save();
            $product = Product::where('id', $user->current_product_id)->first();
            $product->sales -= 1;
            $product->save();
        }
        echo '用户等级过期检测结束' . PHP_EOL;
        echo 'Success ' . date('Y-m-d H:i:s', time()) . PHP_EOL;
    }

    public function CheckOrderStatus()
    {
        echo '订单状态检测开始' . PHP_EOL;
        $orders = Order::where('order_status', 'pending')->where('expired_time', '<', time())->get();
        foreach ($orders as $order) {
            $order->order_status = 'invalid';
            $order->save();
        }
        echo '订单状态检测结束' . PHP_EOL;
    }
}


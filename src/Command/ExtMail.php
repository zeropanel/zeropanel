<?php


namespace App\Command;

use App\Models\{
    Setting,
    User
};

class ExtMail extends Command
{
    public $description = ''
        . '├─=: php xcat ExtMail [选项]' . PHP_EOL
        . '│ ├─ checkUserExpire         - 用户等级还有7天到期提醒' . PHP_EOL
        . '│ ├─ sendNoMail ' . PHP_EOL
        . '│ ├─ sendOldMail' . PHP_EOL;

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

    public static function checkUserExpire()
    {
        echo '用户等级七天到期检测开始' . PHP_EOL;
        $users = User::select(['class_expire', 'id', 'email', 'name'])
            ->get();
        foreach ($users as $user) {
            $now = mktime(0,0,0,date('m'),date('d'),date('Y'));
            $timediff = strtotime(date('Y-m-d', strtotime($user->class_expire))) - $now;
            $days = intval($timediff / 86400);
            if ($days == 7) {
                echo 'Send daily mail to user: ' . $user->id .PHP_EOL;
                $subject = Setting::obtain('website_general_name') . '- 您的用户账户即将过期';
                $text = '您好，系统发现您的账号还剩 ' . $days . ' 天就过期了，请记得及时续费哦~';
                $user->sendMail(
                    $subject,
                    'news/warn.tpl',
                    [
                        'user' => $user,
                        'text' => $text
                    ],
                    [],
                    $_ENV['email_queue']
                );
            }
        }
        echo '用户等级七天到期检测结束' . PHP_EOL;
    }

    public function sendNoMail()
    {
        $users = User::all();
        foreach ($users as $user) {
            if ($user->t == 0) {
                echo 'Send daily mail to user: ' . $user->id .PHP_EOL;
                $user->sendMail(
                    Setting::obtain('website_general_name') . '-期待您的回归',
                    'ext/back.tpl',
                    [
                        'text' => '似乎您在' . Setting::obtain('website_general_name') . '上的流量一直是 0 呢(P.S:也可能是您没有使用 ss 而使用了其他还不能计入流量的方式....)，如果您在使用上遇到了任何困难，请不要犹豫，登录到' . Setting::obtain('website_general_name') . ',您就会知道如何使用了，特别是对于 iOS 用户，最近在使用的优化上大家都付出了很多的努力。期待您的回归～'
                    ],
                    [],
                    $_ENV['email_queue']
                );
            }
        }
    }

    public function sendOldMail()
    {
        $users = User::all();
        foreach ($users as $user) {
            if ($user->t != 0 && $user->t < 1451577599) {
                echo 'Send daily mail to user: ' . $user->id;
                $user->sendMail(
                    Setting::obtain('website_general_name') . '-期待您的回归',
                    'ext/back.tpl',
                    [
                        'text' => '似乎您在 2017 年以来就没有使用过' . Setting::obtain('website_general_name') . '了呢，如果您在使用上遇到了任何困难，请不要犹豫，登录到' . Setting::obtain('website_general_name') . '，您就会知道如何使用了，特别是对于 iOS 用户，最近在使用的优化上大家都付出了很多的努力。期待您的回归～'
                    ],
                    [],
                    $_ENV['email_queue']
                );
            }
        }
    }
}

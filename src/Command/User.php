<?php

namespace App\Command;

use App\Models\User as ModelsUser;
use App\Models\Setting;
use App\Utils\GA;
use App\Utils\Hash;
use App\Utils\Tools;
use Exception;
use Ramsey\Uuid\Uuid;

class User extends Command
{
    public $description = ''
        . '├─=: php xcat User [选项]' . PHP_EOL
        . '│ ├─ getCookie               - 获取指定用户的 Cookie' . PHP_EOL
        . '│ ├─ resetPort               - 重置单个用户端口' . PHP_EOL
        . '│ ├─ createAdmin             - 创建管理员帐号' . PHP_EOL
        . '│ ├─ resetAllPort            - 重置所有用户端口' . PHP_EOL
        . '│ ├─ resetTraffic            - 重置所有用户流量' . PHP_EOL
        . '│ ├─ generateUUID            - 为所有用户生成新的 UUID' . PHP_EOL;

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
     * 重置所有用户流量
     *
     * @return void
     */
    public function resetTraffic()
    {
        try {
            ModelsUser::where('enable', 1)->update([
                'd'          => 0,
                'u'          => 0,
                'last_day_t' => 0,
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
            return;
        }
        echo 'reset traffic successful' . PHP_EOL;
    }

    /**
     * 为所有用户生成新的UUID
     *
     * @return void
     */
    public function generateUUID()
    {
        $users = ModelsUser::all();
        $current_timestamp = time();
        foreach ($users as $user) {
            /** @var ModelsUser $user */
            $user->generateUUID($current_timestamp);
        }
        echo 'generate UUID successful' . PHP_EOL;
    }

    /**
     * 创建 Admin 账户
     *
     * @return void
     */
    public function createAdmin()
    {
        if (count($this->argv) === 3) {
            echo 'add admin/ 创建管理员帐号.....';
            // ask for input
            fwrite(STDOUT, '(1/3)/输入管理员邮箱: ') . PHP_EOL;
            // get input
            $email = strtolower(trim(fgets(STDIN)));
            if ($email == null) {
                die("必须输入管理员邮箱.\r\n");
            }
            
            // write input back
            fwrite(STDOUT, "(2/3) 请输入管理员账户密码：") . PHP_EOL;
            $passwd = trim(fgets(STDIN));
            if ($passwd == null) {
                die("必须输入管理员密码.\r\n");
            }
            
            fwrite(STDOUT, "(3/3) 按 Y 或 y 确认创建：");
            $y = trim(fgets(STDIN));
        } elseif (count($this->argv) === 5) {
            [,,, $email, $passwd] = $this->argv;
            $y = 'y';
        }

        if (strtolower($y) == 'y') {
            echo 'start create admin account';
            $current_timestamp          = time();
            // create admin user
            $configs = Setting::getClass('register');
            // do reg user
            $user                   = new ModelsUser();
            $user->email            = $email;
            $user->password         = Hash::passwordHash($passwd);
            $user->passwd           = Tools::genRandomChar(16);
            $user->uuid             = Uuid::uuid5(Uuid::NAMESPACE_DNS, $email . '|' . $current_timestamp);
            $user->t                = 0;
            $user->u                = 0;
            $user->d                = 0;
            $user->transfer_enable  = 0;
            $user->ref_by           = 0;
            $user->is_admin         = 1;
            $user->signup_date         = date('Y-m-d H:i:s');
            $user->money            = 0;
            $user->class            = 0;
            $user->node_speedlimit  = 0;
            $user->theme            = $_ENV['theme'];

            if ($user->save()) {
                echo '创建成功，请在主页登录' . PHP_EOL;
            } else {
                echo '创建失败，请检查数据库配置' . PHP_EOL;
            }
        } else {
            echo '已取消创建' . PHP_EOL;
        }
    }

    /**
     * 获取 USERID 的 Cookie
     *
     * @return void
     */
    public function getCookie()
    {
        if (count($this->argv) === 4) {
            $user = ModelsUser::find($this->argv[3]);
            $expire_in = 86400 + time();
            echo Hash::cookieHash($user->password, $expire_in) . ' ' . $expire_in;
        }
    }
}

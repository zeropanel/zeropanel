<?php

namespace App\Controllers\WebAPI;

use App\Controllers\BaseController;
use App\Models\{
    Ip,
    Node,
    User,
    TrafficLog,
    NodeOnlineLog
};
use App\Utils\Tools;
use Slim\Http\{
    Request,
    Response
};

class UserController extends BaseController
{
    /**
     * User List
     *
     * @param \Slim\Http\Request    $request
     * @param \Slim\Http\Response   $response
     * @param array                 $args
     *
     * @return \Slim\Http\Response
     */
    public function index($request, $response, $args)
    {
        $node_id = $request->getQueryParam('node_id', '0');

        if ($node_id == '0') {
            $node = Node::where('node_ip', $_SERVER['REMOTE_ADDR'])->first();
            $node_id = $node->id;
        } else {
            $node = Node::where('id', '=', $node_id)->first();
            if ($node == null) {
                return $response->withJson([
                    'ret' => 0,
                ]);
            }
        }
        $node->node_heartbeat = time();
        $node->save();

        // 节点流量耗尽则返回 null
        if (($node->node_bandwidth_limit != 0) && $node->node_bandwidth_limit < $node->node_bandwidth) {
            $users = null;

            return $response->withJson([
                'ret'  => 1,
                'data' => $users
            ]);
        }


        /*
         * 1. 请不要把管理员作为单端口承载用户
         * 2. 请不要把真实用户作为单端口承载用户
         */
        $users_raw = User::where(
            static function ($query) use ($node): void {
                $query->where(
                    static function ($query1) use ($node): void {
                        if ($node->node_group !== 0) {
                            $query1->where('class', '>=', $node->node_class)->where('node_group', '=', $node->node_group);
                        } else {
                            $query1->where('class', '>=', $node->node_class);
                        }
                    }
                )->orwhere('is_admin', 1);
            }
        )->where('enable', 1)->get();

        $users = array();

        if (in_array($node->sort, [11, 14, 15])) {
            $key_list = array('node_speedlimit', 'u', 'd', 'transfer_enable', 'id', 'node_connector', 'uuid', 'alive_ip');
        } else {
            $key_list = array(
                'node_speedlimit', 'u', 'd', 'transfer_enable', 'id', 'passwd', 'node_connector', 'alive_ip'
            );
        }

        $alive_ip = (new \App\Models\Ip)->getUserAliveIpCount();
        foreach ($users_raw as $user_raw) {
            if (isset($alive_ip[strval($user_raw->id)]) && $user_raw->node_connector !== 0) {
                $user_raw->alive_ip = $alive_ip[strval($user_raw->id)];
            }
            if ($user_raw->transfer_enable <= $user_raw->u + $user_raw->d) {
                if ($_ENV['keep_connect'] === true) {
                    // 流量耗尽用户限速至 1Mbps
                    $user_raw->node_speedlimit = 1;
                } else {
                    continue;
                }
            }
            
            $user_raw = Tools::keyFilter($user_raw, $key_list);
            $users[] = $user_raw;
        }


        return $response->withJson([
            'ret'  => 1,
            'data' => $users
        ]);
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function addTraffic($request, $response, $args)
    {
        $params = $request->getQueryParams();

        $data = $request->getParam('data');
        $this_time_total_bandwidth = 0;
        $node_id = $params['node_id'];
        if ($node_id == '0') {
            $node = Node::where('node_ip', $_SERVER['REMOTE_ADDR'])->first();
            $node_id = $node->id;
        }
        $node = Node::find($node_id);

        if ($node == null) {
            $res = [
                'ret' => 0
            ];
            return $response->withJson($res);
        }

        if (count($data) > 0) {
            foreach ($data as $log) {
                $u = $log['u'];
                $d = $log['d'];
                $user_id = $log['user_id'];

                $user = User::find($user_id);

                if ($user == null) {
                    continue;
                }

                $user->t = time();
                $user->u += $u * $node->traffic_rate;
                $user->d += $d * $node->traffic_rate;
                $this_time_total_bandwidth += $u + $d;
                if (!$user->save()) {
                    $res = [
                        'ret' => 0,
                        'data' => 'update failed',
                    ];
                    return $response->withJson($res);
                }

                // log
                $traffic = new TrafficLog();
                $traffic->user_id = $user_id;
                $traffic->u = $u;
                $traffic->d = $d;
                $traffic->node_id = $node_id;
                $traffic->rate = $node->traffic_rate;
                $traffic->traffic = Tools::flowAutoShow(($u + $d) * $node->traffic_rate);
                $traffic->datetime = time();
                $traffic->save();
            }
        }

        $node->node_bandwidth += $this_time_total_bandwidth;
        $node->save();

        $online_log = new NodeOnlineLog();
        $online_log->node_id = $node_id;
        $online_log->online_user = count($data);
        $online_log->log_time = time();
        $online_log->save();

        $res = [
            'ret' => 1,
            'data' => 'ok',
        ];
        return $response->withJson($res);
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function addAliveIp($request, $response, $args)
    {
        $params = $request->getQueryParams();

        $data = $request->getParam('data');
        $node_id = $params['node_id'];
        if ($node_id == '0') {
            $node = Node::where('node_ip', $_SERVER['REMOTE_ADDR'])->first();
            $node_id = $node->id;
        }
        $node = Node::find($node_id);

        if ($node == null) {
            $res = [
                'ret' => 0
            ];
            return $response->withJson($res);
        }
        if (count($data) > 0) {
            foreach ($data as $log) {
                $ip = $log['ip'];
                $userid = $log['user_id'];

                // log
                $ip_log = new Ip();
                $ip_log->userid = $userid;
                $ip_log->nodeid = $node_id;
                $ip_log->ip = $ip;
                $ip_log->datetime = time();
                $ip_log->save();
            }
        }

        $res = [
            'ret' => 1,
            'data' => 'ok',
        ];
        return $response->withJson($res);
    }
}

<?php

namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Models\UserSubscribeLog;
use App\Utils\QQWry;
use Slim\Http\{
    Request,
    Response
};

class SubscribeLogController extends AdminController
{
    /**
     * 后台订阅记录页面
     *
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function index($request, $response, $args)
    {
        $table_config['total_column'] = array(
            'id'                  => 'ID',
            'user_id'             => '用户ID',
            'email'               => '用户邮箱',
            'subscribe_type'      => '类型',
            'request_ip'          => 'IP',
            'location'            => '归属地',
            'request_time'        => '时间',
            'request_user_agent'  => 'User-Agent'
        );
        $table_config['default_show_column'] = array_keys($table_config['total_column']);
        $table_config['ajax_url'] = 'subscribe/ajax';
        $this->view()
            ->assign('table_config', $table_config)
            ->display('admin/subscribe.tpl');
        return $response;
    }

    /**
     * 后台订阅记录页面 AJAX
     *
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function ajaxSubscribeLog($request, $response, $args)
    {
        $query = UserSubscribeLog::getTableDataFromAdmin(
            $request,
            static function (&$order_field) {
                if (in_array($order_field, ['location'])) {
                    $order_field = 'request_ip';
                }
            },
            
        );

        $data  = [];
        $QQWry = new QQWry();
        foreach ($query['datas'] as $value) {
            /** @var UserSubscribeLog $value */

            if ($value->user() == null) {
                UserSubscribeLog::user_is_null($value);
                continue;
            }
            $tempdata                       = [];
            $tempdata['id']                 = $value->id;
            $tempdata['user_id']            = $value->user_id;
            $tempdata['email']              = $value->email;
            $tempdata['subscribe_type']     = $value->subscribe_type;
            $tempdata['request_ip']         = $value->request_ip;
            $tempdata['location']           = $value->location($QQWry);
            $tempdata['request_time']       = $value->request_time;
            $tempdata['request_user_agent'] = $value->request_user_agent;

            $data[] = $tempdata;
        }

        return $response->withJson([
            'draw'            => $request->getParam('draw'),
            'recordsTotal'    => UserSubscribeLog::count(),
            'recordsFiltered' => $query['count'],
            'data'            => $data,
        ]);
    }
}
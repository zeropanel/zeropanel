<?php

namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Models\{
    Ann,
    User,
    Setting
};
use App\Utils\Telegram;
use Slim\Http\{
    Request,
    Response
};

class AnnController extends AdminController
{
    /**
     * 后台公告页面
     *
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function index($request, $response, $args)
    {
        $table_config['total_column'] = array(
            'op'      => '操作',
            'id'      => 'ID',
            'date'    => '日期',
            'content' => '内容'
        );
        $table_config['default_show_column'] = array(
            'op', 'id', 'date', 'content'
        );
        $table_config['ajax_url'] = 'announcement/ajax';
        $this->view()->assign('table_config', $table_config)->display('admin/announcement/index.tpl');
        return $response;
    }

    /**
     * 后台公告页面 AJAX
     *
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function ajax($request, $response, $args)
    {
        $query = Ann::getTableDataFromAdmin(
            $request,
            static function (&$order_field) {
                if (in_array($order_field, ['op'])) {
                    $order_field = 'id';
                }
            }
        );

        $data  = [];
        foreach ($query['datas'] as $value) {
            /** @var Ann $value */

            $tempdata            = [];
            $tempdata['op']      = '<a class="btn btn-brand" href="/admin/announcement/' . $value->id . '/edit">编辑</a> <a class="btn btn-brand-accent" id="delete" value="' . $value->id . '" href="javascript:void(0);" onClick="delete_modal_show(\'' . $value->id . '\')">删除</a>';
            $tempdata['id']      = $value->id;
            $tempdata['date']    = $value->date;
            $tempdata['content'] = $value->content;

            $data[] = $tempdata;
        }

        return $response->withJson([
            'draw'            => $request->getParam('draw'),
            'recordsTotal'    => Ann::count(),
            'recordsFiltered' => $query['count'],
            'data'            => $data,
        ]);
    }

    /**
     * 后台公告创建页面
     *
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function create($request, $response, $args)
    {
        $this->view()->display('admin/announcement/create.tpl');
        return $response;
    }

    /**
     * 后台添加公告
     *
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function add($request, $response, $args)
    {
        $issend   = $request->getParam('issend');
        $vip      = $request->getParam('vip');
        $content  = $request->getParam('content');
        $subject  = Setting::obtain('website_general_name') . '-公告';

        if ($request->getParam('page') == 1) {
            $ann           = new Ann();
            $ann->date     = date('Y-m-d H:i:s');
            $ann->content  = $content;
            $ann->markdown = $request->getParam('markdown');

            if (!$ann->save()) {
                return $response->withJson([
                    'ret' => 0,
                    'msg' => '添加失败'
                ]);
            }
        }
        if ($issend == 1) {
            $beginSend = ($request->getParam('page') - 1) * $_ENV['sendPageLimit'];
            $users     = User::where('class', '>=', $vip)->skip($beginSend)->limit($_ENV['sendPageLimit'])->get();
            foreach ($users as $user) {
                $user->sendMail(
                    $subject,
                    'news/warn.tpl',
                    [
                        'user' => $user,
                        'text' => $content
                    ],
                    [],
                    $_ENV['email_queue']
                );
            }
            if (count($users) == $_ENV['sendPageLimit']) {
                return $response->withJson([
                    'ret' => 2,
                    'msg' => $request->getParam('page') + 1
                ]);
            }
        }
        Telegram::PushToChanel($request->getParam('markdown'));
        if ($issend == 1) {
            $msg = '公告添加成功，邮件发送成功';
        } else {
            $msg = '公告添加成功';
        }
        return $response->withJson([
            'ret' => 1,
            'msg' => $msg
        ]);
    }

    /**
     * 后台编辑公告页面
     *
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function edit($request, $response, $args)
    {
        $ann = Ann::find($args['id']);
        $this->view()->assign('ann', $ann)->display('admin/announcement/edit.tpl');
        return $response;
    }

    /**
     * 后台编辑公告提交
     *
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function update($request, $response, $args)
    {
        $ann           = Ann::find($args['id']);
        $ann->content  = $request->getParam('content');
        $ann->markdown = $request->getParam('markdown');
        $ann->date     = date('Y-m-d H:i:s');
        if (!$ann->save()) {
            return $response->withJson([
                'ret' => 0,
                'msg' => '修改失败'
            ]);
        }
        Telegram::PushToChanel('公告更新：' . PHP_EOL . $request->getParam('markdown'));
        return $response->withJson([
            'ret' => 1,
            'msg' => '修改成功'
        ]);
    }

    /**
     * 后台删除公告
     *
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function delete($request, $response, $args)
    {
        $ann = Ann::find($request->getParam('id'));
        if (!$ann->delete()) {
            return $response->withJson([
                'ret' => 0,
                'msg' => '删除失败'
            ]);
        }
        return $response->withJson([
            'ret' => 1,
            'msg' => '删除成功'
        ]);
    }
}
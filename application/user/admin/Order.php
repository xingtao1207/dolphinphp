<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2019 广东卓锐软件有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------

namespace app\user\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\user\model\Order as OrderModel;
use app\user\model\User as UserModel;
use app\user\model\Role as RoleModel;

/**
 * 消息控制器
 * @package app\user\admin
 */
class Order extends Admin
{
    /**
     * 消息列表
     * @author 蔡伟明 <314013107@qq.com>
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function index($group = 'tab1')
    {
        $map = [];
        // 模块排序
        if ($group == 'tab2') {
            $map['status'] = 1;

        }
        $data_list = OrderModel::where($map)
            ->order($this->getOrder('id DESC'))
            ->paginate();
        $list_tab = [
            'tab1' => ['title' => '全部有效订单', 'url' => url('index', ['group' => 'tab1'])],
            'tab2' => ['title' => '待支付', 'url' => url('index', ['group' => 'tab2'])],
            'tab3' => ['title' => '待发货', 'url' => url('index', ['group' => 'tab2'])],
            'tab4' => ['title' => '待收货', 'url' => url('index', ['group' => 'tab2'])],
            'tab5' => ['title' => '已完成', 'url' => url('index', ['group' => 'tab2'])],
            'tab6' => ['title' => '待商家退款', 'url' => url('index', ['group' => 'tab2'])],
            'tab7' => ['title' => '待退款', 'url' => url('index', ['group' => 'tab2'])],
            'tab8' => ['title' => '退款完成', 'url' => url('index', ['group' => 'tab2'])],
        ];
        return ZBuilder::make('table')
            ->setTableName('admin_message')
            ->addTopButton('add')
            ->addTopButton('delete')
            ->addRightButton('edit')
            ->addRightButton('delete')
            ->addColumns([
                ['id', 'ID'],
                ['order_num', '订单号', 'text.edit'],
                ['status', '状态', 'switch'],
                ['create_time', '下单时间', 'datetime.edit'],
                ['right_button', '操作', 'btn'],
            ])

            ->setRowList($data_list)
            ->setTabNav($list_tab,  'tab1')
            ->fetch();
    }



    /**
     * 新增
     * @author 蔡伟明 <314013107@qq.com>
     * @return mixed
     * @throws \think\Exception
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();

            $data['type'] == '' && $this->error('请填写消息分类');
            $data['content'] == '' && $this->error('请填写消息内容');

            $list = [];
            if ($data['send_type'] == 'uid') {
                !isset($data['uid']) && $this->error('请选择接收消息的用户');
            } else {
                !isset($data['role']) && $this->error('请选择接收消息的角色');
                $data['uid'] = UserModel::where('status', 1)
                    ->where('role', 'in', $data['role'])
                    ->column('id');
                !$data['uid'] && $this->error('所选角色无可发送的用户');
            }

            foreach ($data['uid'] as $uid) {
                $list[] = [
                    'uid_receive' => $uid,
                    'uid_send'    => UID,
                    'type'        => $data['type'],
                    'content'     => $data['content'],
                ];
            }

            $MessageModel = new MessageModel;
            if (false !== $MessageModel->saveAll($list)) {
                $this->success('新增成功', 'index');
            } else {
                $this->error('新增失败');
            }
        }

        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'type', '消息分类'],
                ['textarea', 'content', '消息内容'],
                ['radio', 'send_type', '发送方式', '', ['uid' => '按指定用户', 'role' => '按指定角色'], 'uid'],
                ['select', 'uid', '接收用户', '接收消息的用户', UserModel::where('status', 1)->column('id,nickname'), '', 'multiple'],
                ['select', 'role', '接收角色', '接收消息的角色', RoleModel::where('status', 1)->column('id,name'), '', 'multiple'],
            ])
            ->setTrigger('send_type', 'uid', 'uid')
            ->setTrigger('send_type', 'role', 'role')
            ->fetch();
    }
}

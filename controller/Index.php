<?php
namespace app\goods\controller;

use app\goods\model\Goods;
use think\Controller;
use think\Image;
use think\Log;
use think\Request;
class Index extends Controller
{
    public function index()
    {
        return view('goods/add');
    }
    /**
     * 商品添加
     */
    public function add(Request $request){
        //接收数据
        $data = $request->param();

        $file = $request->file('img');
        //验证数据
        $validate = $this->validate($data,[
            'name|商品名称' => 'require',
            'sale|实际销量' => 'require|number'
        ]);
        if (true !== $validate) {
            $this->error($validate);
        }
        $info = $file->validate(['size'=>1024*1024*2,'ext'=>'jpeg,jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if (!$info) {
            $this->error($file->getError());
        }
        $img = "./uploads/".$info->getSaveName();
        //制作缩略图
        $image = Image::open($img);
        $image->thumb(100,100)->save($img);
        $data['img'] = "/uploads/".$info->getSaveName();
        //添加入库
        $info = Goods::create($data,true);
        if ($info) {
            return $this->redirect('Index/show');
        }
    }

    /**
     * 列表展示
     */
    public function show(){
        //接收参数
        $name = input('name');
        $where = [];
        if ($name != "") {
            $where['name'] = $name;
        }
        //查询数据
        $data = model('Goods')->where($where)->order('create_time',"asc")->paginate(10);
        return view('goods/list',['data'=>$data]);
    }
    /**
     * 删除数据
     */

    public function del(){
        //接收参数
        $id = input('id');
        //验证参数
        if (!is_numeric($id)) {
            $this->error('参数格式不正确');
        }
        //执行删除
        $info = Goods::destroy($id);
        if ($info) {
            //写入日志
            Log::write('删除成功');
            return $this->redirect('Index/show');
        }
    }
}

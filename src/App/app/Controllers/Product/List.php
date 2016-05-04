<?php

class ProductListController extends BaseController
{
    public function __init()
    {
        //方便直观的参数校验设置，支持默认值，支持默认错误提示
        $this->checkRule = array(
            'uid' => "num|1,99999999999|*|10|uid参数非法",
            'type' => "enum|1,2,2|NULL|2|类型只能为1,2,3",
            'name' => "string|3,10|*",
            'url' => "custom|url|NULL|http://www.baidu.com|url不符合要求",
        );
    }

    public function run()
    {
        //统一get 、post 、cookie 、session 、 server的获取和设置
        $uid = $this->request->query('uid');

        $Model_Product = new Model_Product();
        $product_list = $Model_Product->getProductList($uid);


        $data['code'] = '100000';
        $data['msg'] = '操作成功';
        $data['data']['productList'] = $product_list;
        $this->displayJson($data);
    }
}


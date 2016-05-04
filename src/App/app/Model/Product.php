<?php

class Model_Product
{
    function getProductList($uid)
    {
        $Data_Product = new Data_Product();
        return $Data_Product->getProductList($uid);
    }
}

<?php
/*
 * @Author: 000000
 * @Date: 2021-05-31 13:44:29
 * @LastEditTime: 2021-06-01 13:49:34
 * @Description: Forward, no stop
 */
// 全局中间件定义文件
return [
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    \think\middleware\LoadLangPack::class,
    // Session初始化
     \think\middleware\SessionInit::class
];

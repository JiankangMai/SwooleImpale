# SwooleImpale
使用Swoole实现内网穿透

#依赖

#使用方式

1. `composer install` 安装vendor目录

2. 服务端（外网）
 在`conf/server.php`修改服务端配置(一般不需要调整)
`php ./start --server=client` 启动服务端端

3. 客户端（内网）
 在`conf/client.php`修改服务端的ip和注册端口，以及要进行穿透内网web服务端口信息
`php ./start --type=client` 启动客户端（内网）

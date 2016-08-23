1) 基于Laravel4.2写的环信http://www.easemob.com/customer/im服务端扩展包

2) 使用此插件请先查阅并且读懂对应的环信即时通讯云V3.0 服务端集成API http://docs.easemob.com/im/100serverintegration/10intro

3) Composer安装

    在composer.json文件中的require加入
    "require": {
    "zhangxiaoshuai/easemob":"dev-master"
    }
4) 在项目下执行

   $ composer update
5) 发布配置文件

    $ php artisan config:publish Zhangxiaoshuai/Easemob
7) 配置Config

    在项目app/config/packages你会找到一个zhangxiaoshuai/easemob/config.php,然后设置里面的参数
6) 注册包

    在项目的app/config/app.php下的providers方法添加
    'providers' => array(
        'Zhangxiaoshuai\Easemob\EasemobServiceProvider'
    )
    在aliases方法中加入
    'aliases' => array(
        // ...
        'Easemob' =>'Zhangxiaoshuai\Easemob\Facade\Easemob'
    )
7) 配置Config

    在项目app/config/packages你会找到一个zhangxiaoshuai/easemob/config，然后进行配置参数
8）Usage

    // 授权单个注册
        Easemob::createUser($username, $password, $nickname)  // $nickname可以为空
    // 添加好友
        Easemob::addFriend($owner_username, $friend_username)  // $owner_username 是要添加好友的用户名，$friend_username 是被添加的用户名
    // 解除好友关系
        Easemob::deleteFriend($owner_username, $friend_username) // $owner_username 是要解除好友的用户名，$friend_username 是被解除的用户名
    // 查看好友；
        Easemob::showFriends($username)  // $username好友的用户名
    // 查看在线情况
        Easemob::isOnline($username)
    // 查看用户离线消息数
        Easemob::getOfflineMessages($username)
    // 查看某条消息的离线状态
      //  ----deliverd 表示此用户的该条离线消息已经收到
        Easemob::getOfflineMessageStatus($username, $msg_id)  //$msg_id API 有解释
    // 发送文本消息
        Easemob::sendText($from, $target_type, $target, $content);
        // $from 表示消息发送者
        // $target_type ='user' 给用户发消息，并非是群发消息
        // $target 注意这里需要用数组，数组长度建议不大于20，即使只有一个用户，也要用数组 ['u1']，给用户发送时数组元素是用户名
        // $content 发送的内容
    // 查看整个项目的聊天记录
        Easemob::getChatRecord()

    备注：暂时只写了这几个常用的API
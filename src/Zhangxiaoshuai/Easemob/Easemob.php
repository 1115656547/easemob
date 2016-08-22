<?php
/**
 * Created by PhpStorm.
 * User: Zhangxiaoshuai
 * Date: 2016/8/20
 * Time: 14:40
 */
namespace Zhangxiaoshuai\Easemob;

use Illuminate\Support\Facades\Config;

class Easemob
{
    protected $client_id;
    protected $client_secret;
    protected $base_url;

    /*初始化参数*/
    public function __construct()
    {
        $this->client_id = Config::get('easemob::client_id');
        $this->client_secret = Config::get('easemob::client_secret');
        $this->base_url = Config::get('easemob::base_url');
    }

    /*Token*/

    public function createUser($username, $password, $nickname = '')
    {
        $url = $this->base_url . 'users';
        $options = [
            'username' => $username,
            'password' => $password,
            'nickname' => $nickname,
        ];
        $body = json_encode($options);
        $header = array($this->getToken());
        $result = $this->postCurl($url, $body, $header);
        return $result;
    }

    /*
	  授权注册
	*/

    public function getToken()
    {
        if (Session::has('easemob_token')) {
            $easemob_token = Session::get('easemob_token');
            return $easemob_token;
        } else {
            $url = $this->base_url . 'token';
            $options = [
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
            ];
            $body = json_encode($options);
            $tokenResult = $this->postCurl($url, $body, $header = []);
            $result = "Authorization:Bearer " . $tokenResult["access_token"];
            Session::push('easemob_token', $result);
            return $result;
        }
    }

    /*添加好友*/

    function postCurl($url, $body, $header, $type = "POST")
    {
        //1.创建一个curl资源
        $ch = curl_init();
        //2.设置URL和相应的选项
        curl_setopt($ch, CURLOPT_URL, $url);//设置url
        //1)设置请求头
        //array_push($header, 'Accept:application/json');
        //array_push($header,'Content-Type:application/json');
        //array_push($header, 'http:multipart/form-data');
        //设置为false,只会获得响应的正文(true的话会连响应头一并获取到)
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 设置超时限制防止死循环
        //设置发起连接前的等待时间，如果设置为0，则无限等待。
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //2)设备请求体
        if (count($body) > 0) {
            //$b=json_encode($body,true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);//全部数据使用HTTP协议中的"POST"操作来发送。
        }
        //设置请求头
        if (count($header) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        //上传文件相关设置
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);// 从证书中检查SSL加密算

        //3)设置提交方式
        switch ($type) {
            case "GET":
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                break;
            case "PUT"://使用一个自定义的请求信息来代替"GET"或"HEAD"作为HTTP请									                     求。这对于执行"DELETE" 或者其他更隐蔽的HTT
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
        }


        //4)在HTTP请求中包含一个"User-Agent: "头的字符串。-----必设
        curl_setopt($ch, CURLOPT_USERAGENT, 'SSTS Browser/1.0');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)'); // 模拟用户使用的浏览器
        //5)


        //3.抓取URL并把它传递给浏览器
        $res = curl_exec($ch);
        $result = json_decode($res, true);
        //4.关闭curl资源，并且释放系统资源
        curl_close($ch);
        if (empty($result))
            return $res;
        else
            return $result;
    }

    /*解除好友关系*/

    public function addFriend($owner_username, $friend_username)
    {
        //$owner_username 是要添加好友的用户名，$friend_username 是被添加的用户名。
        $url = $this->base_url . 'users/' . $owner_username . '/contacts/users/' . $friend_username;
        $header = array($this->getToken(), 'Content-Type:application/json');
        /*处理火狐浏览器不兼容的问题*/
        if (is_array($header[0])) {
            array_unshift($header[0], $header[1]);
            $header = $header[0];
        }
        $result = $this->postCurl($url, '', $header);
        return $result;
    }

    /*查看好友*/

    public function deleteFriend($owner_username, $friend_username)
    {
        //$owner_username 是要添加好友的用户名，$friend_username 是被添加的用户名。
        $url = $this->base_url . 'users/' . $owner_username . '/contacts/users/' . $friend_username;
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'DELETE');
        return $result;
    }

    /*查看在线情况*/

    public function showFriends($username)
    {
        $url = $this->base_url . 'users/' . $username . '/contacts/users';
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'GET');
        return $result;
    }

    /*查看用户离线消息数*/

    public function isOnline($username)
    {
        $url = $this->base_url . 'users/' . $username . '/status';
        $header = array($this->getToken(), 'Content-Type:application/json');
        $result = $this->postCurl($url, '', $header, 'GET');
        return $result;
    }

    /*
	查看某条消息的离线状态
	----deliverd 表示此用户的该条离线消息已经收到
    */

    public function getOfflineMessages($username)
    {
        $url = $this->base_url . 'users/' . $username . '/offline_msg_count';
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'GET');
        return $result;
    }

    public function getOfflineMessageStatus($username, $msg_id)
    {
        $url = $this->base_url . 'users/' . $username . '/offline_msg_count/' . $msg_id;
        $header = array($this->getToken(), 'Content-Type:application/json');
        $result = $this->postCurl($url, '', $header, 'GET');
        return $result;
    }

//--------------------------------------------------------发送消息
    /*
        发送文本消息
    */

    public function getChatRecord()
    {
        $url = $this->base_url . 'chatmessages';
        $header = array($this->getToken(), 'Content-Type:application/json');
        $result = $this->postCurl($url, '', $header, 'GET');
        return $result;
    }


//--------------------------------------------------------postCurl

    public function sendText($from, $target_type, $target, $content)
    {
        $url = $this->base_url . 'messages';
        $body['target_type'] = $target_type;
        $body['target'] = $target;
        $options['type'] = "txt";
        $options['msg'] = $content;
        $body['msg'] = $options;
        $body['from'] = $from;
        $b = json_encode($body);
        $header = array($this->getToken(), 'Content-Type:application/json');
        $result = $this->postCurl($url, $b, $header);
        return $result;
    }

}
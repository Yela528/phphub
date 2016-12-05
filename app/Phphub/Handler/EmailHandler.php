<?php

namespace Phphub\Handler;

use App\Models\User;
use App\Models\Reply;
use App\Models\Topic;
use App\Models\Mention;
use App\Models\Append;
use App\Models\NotificationMailLog;
use Illuminate\Mail\Message;
use Mail;
use Naux\Mail\SendCloudTemplate;
use Jrean\UserVerification\Facades\UserVerification;

class EmailHandler
{
    protected $methodMap = [
        'at'                   => 'sendAtNotifyMail',
        'attention'            => 'sendAttentionNotifyMail',
        'vote_append'          => 'sendVoteAppendNotifyMail',
        'comment_append'       => 'sendCommentAppendNotifyMail',
        'follow'               => 'sendFollowNotifyMail',
        'new_reply'            => 'sendNewReplyNotifyMail',
        'reply_upvote'         => 'sendReplyUpvoteNotifyMail',
        'topic_attent'         => 'sendTopicAttentNotifyMail',
        'topic_mark_excellent' => 'sendTopicMarkExcellentNotifyMail',
        'topic_upvote'         => 'sendTopicUpvoteNotifyMail',
    ];

    protected $type;
    protected $fromUser;
    protected $toUser;
    protected $topic;
    protected $reply;
    protected $body;

    public function sendMaintainerWorksMail(User $user, $timeFrame, $content)
    {
        $reply = [
            'name'       => $user->name,
            'time_frame' => $timeFrame,
            'content'    => $content,
        ];

        Mail::send('emails.reply',$reply,function ($m) use ($user){
            $m->to($user->email)->subject('管理员工作统计');
            $this->generateMailLog($this->reply->body);

        });

//        Mail::send('emails.fake', [], function (Message $message) use ($user, $timeFrame, $content) {
//            $message->subject('管理员工作统计');
//
//            $message->getSwiftMessage()->setBody(new SendCloudTemplate('maintainer_works', [
//                'name'       => $user->name,
//                'time_frame' => $timeFrame,
//                'content'    => $content,
//            ]));
//
//            $message->to($user->email);
//        });
    }

    public function sendActivateMail(User $user)
    {
        UserVerification::generate($user);
        $token = $user->verification_token;

        $url =url('verification', $user->verification_token).'?email='.urlencode($user->email);

        Mail::send('emails.verify',['name'=>$user->name,'url'=> $url],function ($m) use ($user){
            $m->to($user->email)->subject(lang('Please verify your email address'));
        });


    }

    public function sendNotifyMail($type, User $fromUser, User $toUser, Topic $topic = null, Reply $reply = null, $body = null)
    {
        if (
            !isset($this->methodMap[$type])
            || $toUser->email_notify_enabled != 'yes'
            || $toUser->id == $fromUser->id
            || !$toUser->email || $toUser->verified != 1
        ) {
            return false;
        }

        $this->topic = $topic;
        $this->reply = $reply;
        $this->body = $body;
        $this->fromUser = $fromUser;
        $this->toUser = $toUser;
        $this->type = $type;

        $method = $this->methodMap[$type];
        $this->$method();
    }

    protected function sendNewReplyNotifyMail()
    {
        if (!$this->reply) {
            return false;
        }

        $reply = [
            'replier' => url(route('users.show', $this->fromUser->id)) ,
            'name' => $this->fromUser->name,
            'articleAddress' => url(route('topics.show', $this->reply->topic_id)),
            'article' => $this->reply->topic->title,
            'content' =>  $this->reply->body

        ];

        Mail::send('emails.reply',$reply,function ($m){
           $m->to($this->toUser->email)->subject(lang('Your topic have new reply'));
            $this->generateMailLog($this->reply->body);

        });



//        Mail::send('emails.fake', [], function (Message $message) {
//            $message->subject(lang('Your topic have new reply'));
//
//            $message->getSwiftMessage()->setBody(new SendCloudTemplate('notification_mail', [
//                'name'     => "<a href='" . url(route('users.show', $this->fromUser->id)) . "' target='_blank'>{$this->fromUser->name}</a>",
//                'action'   => " 回复了你的主题: <a href='" . url(route('topics.show', $this->reply->topic_id)) . "' target='_blank'>{$this->reply->topic->title}</a>
//                              <br /><br />内容如下：<br />",
//                'content'  => $this->reply->body,
//            ]));
//
//            $message->to($this->toUser->email);
//
//            $this->generateMailLog($this->reply->body);
//        });
    }

    protected function sendAtNotifyMail()
    {
        if (!$this->reply) {
            return false;
        }

        $reply = [
            'replier' => url(route('users.show', $this->fromUser->id)) ,
            'name' => $this->fromUser->name,
            'articleAddress' => url(route('topics.show', $this->reply->topic_id)),
            'article' => $this->reply->topic->title,
            'content' =>  $this->reply->body

        ];

        Mail::send('emails.notify',$reply,function ($m){
            $m->to($this->toUser->email)->subject('有用户在主题中提及你');
            $this->generateMailLog($this->reply->body);

        });

//        Mail::send('emails.fake', [], function (Message $message) {
//            $message->subject('有用户在主题中提及你');
//
//            $message->getSwiftMessage()->setBody(new SendCloudTemplate('notification_mail', [
//                'name'     => "<a href='" . url(route('users.show', $this->fromUser->id)) . "' target='_blank'>{$this->fromUser->name}</a>",
//                'action'   => " 在主题: <a href='" . url(route('topics.show', $this->reply->topic_id)) . "' target='_blank'>{$this->reply->topic->title}</a> 中提及了你
//                              <br /><br />内容如下：<br />",
//                'content'  => $this->reply->body,
//            ]));
//
//            $message->to($this->toUser->email);
//            $this->generateMailLog($this->reply->body);
//        });
    }

    protected function sendTopicAttentNotifyMail()
    {
        if (!$this->topic) {
            return false;
        }
        $reply = [
            'replier' => url(route('users.show', $this->fromUser->id)) ,
            'name' => $this->fromUser->name,
            'articleAddress' => url(route('topics.show', $this->reply->topic_id)),
            'article' => $this->topic->title


        ];

        Mail::send('emails.topicAttent',$reply,function ($m){
            $m->to($this->toUser->email)->subject('有用户关注了你的主题');
            $this->generateMailLog($this->reply->body);

        });

//
//        Mail::send('emails.fake', [], function (Message $message) {
//            $message->subject('有用户关注了你的主题');
//
//            $message->getSwiftMessage()->setBody(new SendCloudTemplate('notification_mail', [
//                'name'    => "<a href='" . url(route('users.show', $this->fromUser->id)) . "' target='_blank'>{$this->fromUser->name}</a>",
//                'action'  => " 关注了你的主题: <a href='" . url(route('topics.show', $this->topic->id)) . "' target='_blank'>{$this->topic->title}</a>",
//                'content' => '',
//            ]));
//
//            $message->to($this->toUser->email);
//            $this->generateMailLog();
//        });
    }

    protected function sendAttentionNotifyMail()
    {
        if (!$this->reply) {
            return false;
        }

        $reply = [
            'replier' => url(route('users.show', $this->fromUser->id)) ,
            'name' => $this->fromUser->name,
            'articleAddress' => url(route('topics.show', $this->reply->topic_id)),
            'article' => $this->reply->topic->title,
            'content' =>  $this->reply->body

        ];

        Mail::send('emails.attention',$reply,function ($m){
            $m->to($this->toUser->email)->subject('有用户回复了你关注的主题');
            $this->generateMailLog($this->reply->body);

        });

//
//        Mail::send('emails.fake', [], function (Message $message) {
//            $message->subject('有用户回复了你关注的主题');
//
//            $message->getSwiftMessage()->setBody(new SendCloudTemplate('notification_mail', [
//                'name'     => "<a href='" . url(route('users.show', $this->fromUser->id)) . "' target='_blank'>{$this->fromUser->name}</a>",
//                'action'   => " 回复了你关注的主题: <a href='" . url(route('topics.show', $this->reply->topic_id)) . "' target='_blank'>{$this->reply->topic->title}</a>
//                              <br /><br />回复内容如下：<br />",
//                'content'  => $this->reply->body,
//            ]));
//
//            $message->to($this->toUser->email);
//            $this->generateMailLog($this->reply->body);
//        });
    }

    protected function sendVoteAppendNotifyMail()
    {
        if (!$this->body || !$this->topic) {
            return false;
        }

        $reply = [
            'articleAddress' => url(route('topics.show', $this->topic->id)),
            'article' => $this->topic->title,
            'content' =>  $this->body

        ];

        Mail::send('emails.voteAppend',$reply,function ($m){
            $m->to($this->toUser->email)->subject('你关注的话题有新附言');
            $this->generateMailLog($this->body);

        });



//        Mail::send('emails.fake', [], function (Message $message) {
//            $message->subject('你关注的话题有新附言');
//
//            $message->getSwiftMessage()->setBody(new SendCloudTemplate('notification_mail', [
//                'name'     => "",
//                'action'   => " 你关注的话题: <a href='" . url(route('topics.show', $this->topic->id)) . "' target='_blank'>{$this->topic->title}</a> 有新附言
//                              <br /><br />附言内容如下：<br />",
//                'content'  => $this->body,
//            ]));
//
//            $message->to($this->toUser->email);
//            $this->generateMailLog($this->body);
//        });
    }

    protected function sendCommentAppendNotifyMail()
    {
        if (!$this->body || !$this->topic) {
            return false;
        }

        $reply = [
            'articleAddress' => url(route('topics.show', $this->topic->id)),
            'article' => $this->topic->title,
            'content' =>  $this->body

        ];

        Mail::send('emails.sendCommentAppend',$reply,function ($m){
            $m->to($this->toUser->email)->subject('你留言的话题有新附言');
            $this->generateMailLog($this->body);

        });


//        Mail::send('emails.fake', [], function (Message $message) {
//            $message->subject('你留言的话题有新附言');
//
//            $message->getSwiftMessage()->setBody(new SendCloudTemplate('notification_mail', [
//                'name'     => "",
//                'action'   => " 你留言的话题: <a href='" . url(route('topics.show', $this->topic->id)) . "' target='_blank'>{$this->topic->title}</a> 有新附言
//                              <br /><br />附言内容如下：<br />",
//                'content'  => $this->body,
//            ]));
//
//            $message->to($this->toUser->email);
//            $this->generateMailLog($this->body);
//        });
    }

    protected function sendFollowNotifyMail()
    {

        $reply = [
            'replier' => url(route('users.show', $this->fromUser->id)) ,
            'name' => $this->fromUser->name,
        ];

        Mail::send('emails.follow',$reply,function ($m){
            $m->to($this->toUser->email)->subject('有用户关注了你');
            $this->generateMailLog('');

        });

//        Mail::send('emails.fake', [], function (Message $message) {
//            $message->subject('有用户关注了你');
//
//            $message->getSwiftMessage()->setBody(new SendCloudTemplate('notification_mail', [
//                'name'     => "<a href='" . url(route('users.show', $this->fromUser->id)) . "' target='_blank'>{$this->fromUser->name}</a>",
//                'action'   => " 关注了你",
//                'content'  => "",
//            ]));
//
//            $message->to($this->toUser->email);
//            $this->generateMailLog('');
//        });
    }

    protected function sendReplyUpvoteNotifyMail()
    {
        if (!$this->reply) {
            return false;
        }

        $reply = [
            'replier' => url(route('users.show', $this->fromUser->id)) ,
            'name' => $this->fromUser->name,
            'articleAddress' => url(route('topics.show', $this->reply->topic_id)),
            'article' => $this->reply->topic->title,
            'content' =>  $this->reply->body

        ];

        Mail::send('emails.replyupvote',$reply,function ($m){
            $m->to($this->toUser->email)->subject('有用户赞了你的回复');
            $this->generateMailLog($this->reply->body);

        });


//        Mail::send('emails.fake', [], function (Message $message) {
//            $message->subject('有用户赞了你的回复');
//
//            $message->getSwiftMessage()->setBody(new SendCloudTemplate('notification_mail', [
//                'name'     => "<a href='" . url(route('users.show', $this->fromUser->id)) . "' target='_blank'>{$this->fromUser->name}</a>",
//                'action'   => " 赞了你的回复: <a href='" . url(route('topics.show', $this->reply->topic_id)) . "' target='_blank'>{$this->reply->topic->title}</a>
//                              <br /><br />你的回复内容如下：<br />",
//                'content'  => $this->reply->body,
//            ]));
//
//            $message->to($this->toUser->email);
//            $this->generateMailLog($this->reply->body);
//        });
    }

    protected function sendTopicMarkExcellentNotifyMail()
    {
        if (!$this->topic) {
            return false;
        }
        $reply = [
            'replier' => url(route('users.show', $this->fromUser->id)) ,
            'name' => $this->fromUser->name,
            'articleAddress' => url(route('topics.show', $this->reply->topic_id)),
            'article' => $this->reply->topic->title,
        ];

        Mail::send('emails.topicMarkExcellent',$reply,function ($m){
            $m->to($this->toUser->email)->subject('管理员推荐了你的主题');
            $this->generateMailLog('');

        });


//        Mail::send('emails.fake', [], function (Message $message) {
//            $message->subject('管理员推荐了你的主题');
//
//            $message->getSwiftMessage()->setBody(new SendCloudTemplate('notification_mail', [
//                'name'    => "<a href='" . url(route('users.show', $this->fromUser->id)) . "' target='_blank'>{$this->fromUser->name}</a>",
//                'action'  => " 推荐了你的主题: <a href='" . url(route('topics.show', $this->topic->id)) . "' target='_blank'>{$this->topic->title}</a>",
//                'content' => '',
//            ]));
//
//            $message->to($this->toUser->email);
//            $this->generateMailLog();
//        });
    }

    protected function sendTopicUpvoteNotifyMail()
    {
        if (!$this->topic) {
            return false;
        }
        $reply = [
            'replier' => url(route('users.show', $this->fromUser->id)) ,
            'name' => $this->fromUser->name,
            'articleAddress' => url(route('topics.show', $this->reply->topic_id)),
            'article' => $this->reply->topic->title,
        ];

        Mail::send('emails.topicUpvote',$reply,function ($m){
            $m->to($this->toUser->email)->subject('有用户赞了你的主题');
            $this->generateMailLog('');

        });

//        Mail::send('emails.fake', [], function (Message $message) {
//            $message->subject('有用户赞了你的主题');
//
//            $message->getSwiftMessage()->setBody(new SendCloudTemplate('notification_mail', [
//                'name'    => "<a href='" . url(route('users.show', $this->fromUser->id)) . "' target='_blank'>{$this->fromUser->name}</a>",
//                'action'  => " 赞了你的主题: <a href='" . url(route('topics.show', $this->topic->id)) . "' target='_blank'>{$this->topic->title}</a>",
//                'content' => '',
//            ]));
//
//            $message->to($this->toUser->email);
//            $this->generateMailLog();
//        });
    }

    protected function generateMailLog($body = '')
    {
        $data = [];
        $data['from_user_id'] = $this->fromUser->id;
        $data['user_id'] = $this->toUser->id;
        $data['type'] = $this->type;
        $data['body'] = $body;
        $data['reply_id'] = $this->reply ? $this->reply->id : 0;
        $data['topic_id'] = $this->topic ? $this->topic->id : 0;

        NotificationMailLog::create($data);
    }


}

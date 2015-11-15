<?php

namespace urukalo\CH;

/**
 * Description of poll
 *
 * @author milan
 */
class Poll extends \Illuminate\Database\Eloquent\Model {

    public $timestamps = false;

    public function answers() {
        return $this->hasMany('urukalo\CH\Answers', 'idPoll');
    }

    public function user_polls() {
        return $this->hasMany('urukalo\CH\UserPolls', 'idPoll');
    }

}

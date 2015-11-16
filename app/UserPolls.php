<?php

namespace urukalo\CH;

/**
 * Model for user response on poll
 *
 * @author milan
 */
class UserPolls extends \Illuminate\Database\Eloquent\Model {

    public function poll() {
        return $this->belongsTo('urukalo\CH\Poll', 'idPoll');
    }

    public function answer() {
        return $this->belongsTo('urukalo\CH\Answers', 'idAnswerSelected');
    }

}

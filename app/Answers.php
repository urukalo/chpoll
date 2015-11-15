<?php

namespace urukalo\CH;

/**
 * Answers model
 *
 * @author milan
 */
class Answers extends \Illuminate\Database\Eloquent\Model {

    protected $fillable = ['text'];
    public $timestamps = false;

    public function poll() {
        return $this->belongsTo('urukalo\CH\Poll', 'idPoll');
    }

}

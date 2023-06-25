<?php

namespace LucasBrito\Rateable;

use Illuminate\Support\Facades\Auth;

trait Rateable
{
    /**
     * This model has many ratings.
     *
     * @param mixed $ref
     * @param mixed $rating
     * @param mixed $value
     * @param string $comment
     *
     * @return Rating
     */
    public function rate($ref, $value, $comment = null)
    {
        $rating = new Rating();
        $rating->ref = $ref;
        $rating->rating = $value;
        $rating->comment = $comment;
        $rating->user_id = Auth::id();

        $this->ratings()->save($rating);
    }

    public function rateOnce($ref, $value, $comment = null)
    {
        $rating = Rating::query()
            ->where('rateable_type', '=', $this->getMorphClass())
            ->where('rateable_id', '=', $this->id)
            ->where('ref', '=', $ref)
            ->where('user_id', '=', Auth::id())
            ->first();

        if ($rating) {
            $rating->ref = $ref;
            $rating->rating = $value;
            $rating->comment = $comment;
            $rating->save();
        } else {
            $this->rate($ref, $value, $comment);
        }
    }

    public function ratings()
    {
        return $this->morphMany('lucasbrito-wdt\Rateable\Rating', 'rateable');
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    public function sumRating()
    {
        return $this->ratings()->sum('rating');
    }

    public function timesRated()
    {
        return $this->ratings()->count();
    }

    public function usersRated()
    {
        return $this->ratings()->groupBy('user_id')->pluck('user_id')->count();
    }

    public function userAverageRating()
    {
        return $this->ratings()->where('user_id', Auth::id())->avg('rating');
    }

    public function userSumRating()
    {
        return $this->ratings()->where('user_id', Auth::id())->sum('rating');
    }

    public function ratingPercent($max = 5)
    {
        $quantity = $this->ratings()->count();
        $total = $this->sumRating();

        return ($quantity * $max) > 0 ? $total / (($quantity * $max) / 100) : 0;
    }

    // Getters

    public function getAverageRatingAttribute()
    {
        return $this->averageRating();
    }

    public function getSumRatingAttribute()
    {
        return $this->sumRating();
    }

    public function getUserAverageRatingAttribute()
    {
        return $this->userAverageRating();
    }

    public function getUserSumRatingAttribute()
    {
        return $this->userSumRating();
    }
}

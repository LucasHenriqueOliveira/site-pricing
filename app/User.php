<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getPublishers($user_id, $is_superuser) {

        if($is_superuser) {
            $publishers = DB::select('
                SELECT p.publisher_id, p.name, r.client_fraction, p.site_name, p.site_code, p.site_ga
                    FROM `publisher` as p INNER JOIN `revenue_share` as r
                    ON p.publisher_id = r.publisher_id
                    WHERE p.active = 1
                    ORDER BY p.site_code ASC;
            ');
        } else {
            $publishers = DB::select("
                SELECT p.publisher_id, p.name, r.client_fraction, p.site_name, p.site_code, p.site_ga
                FROM `publisher` as p INNER JOIN `revenue_share` as r
                	ON p.publisher_id = r.publisher_id
                	INNER JOIN `user_publisher` as up ON up.publisher_id = p.publisher_id
                	INNER JOIN `users` as u ON u.id = up.user_id
                WHERE p.active = 1 AND u.id = :user_id
                ORDER BY p.site_code ASC
            ", ['user_id' => $user_id]);
        }

        $pubs = [];
        foreach ($publishers as $publisher) {
            $pub['publisher_id'] = (int)$publisher->publisher_id;
            $pub['name'] = $publisher->name;
            $pub['site_name'] = $publisher->site_name;
            $pub['site_code'] = $publisher->site_code;
            $pub['site_ga'] = $publisher->site_ga;
            $pub['client_fraction'] = $publisher->client_fraction;
            $pubs[] = $pub;
        }
        return $pubs;
    }
}
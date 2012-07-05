<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

class PostModel extends Orm\Entity
{
    public static $columns = array('id', 'title', 'slug', 'text', 'draft_text', 'is_published', 'user_id', 'created_at', 'updated_at', 'published_at');
    public static $table = 'posts';
    public static $primary_key = 'id';

    protected function validations()
    {
        $this->validates(
            array(
                'title', 'text'
            ),
            array(
                'Presence' => true,
            )
        );
    }

    public static function published()
    {
        return self::find()
            ->where(
                array(
                    'published_at < ? AND is_published = ?', 
                    date('Y-m-d H:i:s'), 1
                )
            );
    }

    public static function findByUrl($params)
    {
        $slug = array_pop($params);
        $date = implode("-",$params);

        return self::find()
            ->where(
                array(
                    'slug'=>$slug,
                    'DATE(published_at)'=>$date,
                    'is_published'=>1
                )
            )->fetch();       
    }
}

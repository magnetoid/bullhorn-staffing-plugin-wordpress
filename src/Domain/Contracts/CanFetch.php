<?php


namespace WPBullhornStaffing\Domain\Contracts;


interface CanFetch
{
    public static function find($id);

    public static function fromObject($data);

}
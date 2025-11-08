<?php


namespace WPBullhornStaffing\Domain\Options;

/**
 * Class CountryOptions
 * @package WPBullhornStaffing\Domain\Options
 *
 * @example
 * Array
 * (
 *  [0] => stdClass Object
 *      (
 *      [value] => 2378
 *      [label] => - None Specified -
 *      )
 *  [1] => stdClass Object
 *      (
 *      [value] => 2185
 *      [label] => Afghanistan
 *      )
 */
class CountryOptions extends AbstractOptions
{
    protected static $instance;

    public function getOptionsType(): string
    {
        return 'Country';
    }
}
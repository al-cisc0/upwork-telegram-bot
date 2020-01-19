<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['name','value','description'];
    /**
     * Get value of setting by name
     *
     * @param string $name
     * @return string
     */
    static function getSetting(string $name) : string
    {
        $val = '';
        $setting = self::where('name','=',$name)->first();
        if ($setting) {
            $val = $setting->value;
        }
        return $val;
    }

    /**
     * Set setting by name
     *
     * @param string $name
     * @param string $value
     */
    static function setSetting(string $name, string $value)
    {
        self::updateOrCreate(['name' => $name],['value' => $value]);
    }
}

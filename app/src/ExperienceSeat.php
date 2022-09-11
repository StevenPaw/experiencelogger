<?php

namespace App\Docs;

use SilverStripe\ORM\DataObject;

/**
 * Class \App\Database\ExperienceSeat
 *
 * @property string $Row
 * @property string $Seat
 * @property int $Coord1
 * @property int $Coord2
 * @property int $ParentID
 * @method \App\Docs\Experience Parent()
 */
class ExperienceSeat extends DataObject
{
    private static $db = [
        "Row" => "Varchar(255)",
        "Seat" => "Varchar(255)",
        "Coord1" => "Int",
        "Coord2" => "Int",
    ];

    private static $has_one = [
        "Parent" => Experience::class
    ];

    private static $default_sort = "Title ASC";

    private static $field_labels = [
        "Row" => "Reihe",
        "Seat" => "Sitz",
        "Coord1" => "X-Koordinate",
        "Coord2" => "Y-Koordinate",
    ];

    private static $summary_fields = [
        "Title" => "Titel",
    ];

    private static $searchable_fields = [
        "Row", "Seat"
    ];

    private static $table_name = "ExperienceSeat";

    private static $singular_name = "Sitz";
    private static $plural_name = "Sitze";

    private static $url_segment = "seat";

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        return $fields;
    }
}

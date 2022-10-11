<?php

namespace App\ExperienceDatabase;

use SilverStripe\Assets\File;
use App\Overview\LocationPage;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\GroupedList;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Security\Permission;

/**
 * Class \App\Database\Experience
 *
 * @property string $Title
 * @property string $State
 * @property bool $HasSeats
 * @property bool $HasTrains
 * @property bool $HasWagons
 * @property bool $HasBoats
 * @property bool $HasScore
 * @property string $Description
 * @property int $ImageID
 * @property int $ParentID
 * @property int $TypeID
 * @property int $AreaID
 * @property int $LayoutSVGID
 * @method \SilverStripe\Assets\Image Image()
 * @method \App\ExperienceDatabase\ExperienceLocation Parent()
 * @method \App\ExperienceDatabase\ExperienceType Type()
 * @method \App\ExperienceDatabase\Experience Area()
 * @method \SilverStripe\Assets\File LayoutSVG()
 * @method \SilverStripe\ORM\DataList|\App\ExperienceDatabase\ExperienceData[] ExperienceData()
 * @method \SilverStripe\ORM\DataList|\App\ExperienceDatabase\ExperienceSeat[] ExperienceSeats()
 */
class Experience extends DataObject
{
    private static $db = [
        "Title" => "Varchar(255)",
        "State" => "Enum('Active, Defunct, In Maintenance, Other', 'Active')",
        "HasSeats" => "Boolean",
        "HasTrains" => "Boolean",
        "HasWagons" => "Boolean",
        "HasBoats" => "Boolean",
        "HasScore" => "Boolean",
        "Description" => "HTMLText",
    ];

    private static $api_access = ['view' => ['Title', 'ExperienceType', 'ExperienceArea', 'State', 'Description', 'ExperienceImage', 'ParentID']];

    private static $has_one = [
        "Image" => Image::class,
        "Parent" => ExperienceLocation::class,
        "Type" => ExperienceType::class,
        "Area" => Experience::class,
        "LayoutSVG" => File::class,
    ];

    private static $has_many = [
        "ExperienceData" => ExperienceData::class,
        "ExperienceSeats" => ExperienceSeat::class,
    ];

    private static $belongs_many = [
        "Experiences" => Experience::class,
    ];

    private static $owns = [
        "Image",
        "LayoutSVG",
        "ExperienceData",
        "ExperienceSeats",
    ];

    private static $summary_fields = [
        "Title" => "Title",
        "Type.Title" => "Type",
        "Area.Title" => "Area",
        "State" => "Status",
    ];

    private static $field_labels = [
        "Title" => "Title",
        "ExperienceType" => "Type",
        "State" => "Status",
        "Description" => "Description",
        "LayoutSVG" => "Seat-Layout",
        "Image" => "Image",
        "Parent.Title" => "Location",
        "Area" => "Area",
        "HasScore" => "Has Score",
        "HasSeats" => "Has Seats in general",
        "HasTrains" => "Has Trains",
        "HasWagons" => "Has Wagons",
        "HasBoats" => "Has Boats",
    ];

    private static $default_sort = "State ASC, TypeID ASC, AreaID ASC, Title ASC";

    private static $table_name = "Experience";

    private static $singular_name = "Experience";
    private static $plural_name = "Experiences";

    private static $defaults = [
        "State" => "Active",
        "HasScore" => false,
        "HasSeats" => true,
    ];

    private static $url_segment = "experience";

    public function getExperienceImage()
    {
        return $this->Image()->exists() ? $this->Image()->getAbsoluteURL() : null;
    }

    public function getExperienceType()
    {
        return $this->Type()->exists() ? $this->Type()->Title : null;
    }

    public function getExperienceArea()
    {
        return $this->Area()->exists() ? $this->Area()->Title : null;
    }

    public function getLink()
    {
        $locationsHolder = LocationPage::get()->first();
        return $locationsHolder->Link("experience/") . $this->getFormattedName();
    }

    public function getAddLogLink()
    {
        $locationsHolder = LocationPage::get()->first();
        return $locationsHolder->Link("addLog/") . $this->getFormattedName();
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName("ParentID");
        $fields->insertAfter('Title', new DropdownField('TypeID', 'Type', ExperienceType::get()->map('ID', 'Title')));

        $areatypeID = ExperienceType::get()->filter('Title', 'Area')->first()->ID;
        $parentID = $this->ParentID;
        if ($areatypeID) {
            $experiencemap = Experience::get()->filter([
                'TypeID' => $areatypeID,
                'ParentID' => $parentID,
            ])->map('ID', 'Title');

            $fields->insertAfter('TypeID', new DropdownField('AreaID', 'Area', $experiencemap))->setHasEmptyDefault(true)->setEmptyString("- Not inside Area -");
        }
        return $fields;
    }

    public function canView($member = null)
    {
        return true;
    }

    public function canEdit($member = null)
    {
        return Permission::check('CMS_ACCESS_NewsAdmin', 'any', $member);
    }

    public function canDelete($member = null)
    {
        return Permission::check('CMS_ACCESS_NewsAdmin', 'any', $member);
    }

    public function canCreate($member = null, $context = [])
    {
        return Permission::check('CMS_ACCESS_NewsAdmin', 'any', $member);
    }

    public function getFormattedName()
    {
        $formattedName = $this->ID . "--" . $this->Title;
        return $formattedName;
    }

    public function getSortedTrains()
    {
        return GroupedList::create($this->ExperienceSeats()->sort('Train ASC'))->GroupedBy("Train");
    }

    public function getSortedWagons($train)
    {
        return GroupedList::create($this->ExperienceSeats()->filter('Train', $train)->sort('Wagon ASC'))->GroupedBy("Wagon");
    }

    public function getSortedRows($train, $wagon)
    {
        return GroupedList::create($this->ExperienceSeats()->filter([
            'Train' => $train,
            'Wagon' => $wagon,
        ])->sort('Row ASC'))->GroupedBy("Wagon");
    }

    public function getSortedSeats($train, $wagon, $row)
    {
        return $this->ExperienceSeats()->filter([
            "Train" => $train,
            "Wagon" => $wagon,
            "Row" => $row,
        ])->sort('Seat');
    }
}

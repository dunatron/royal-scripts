<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 1/03/17
 * Time: 10:10 AM
 */
class Event extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(100)',
        'Description' => 'Text',
        'StartDate' =>  'Date',
        'EndDate'   =>  'Date',
        'Address'  =>  'Text',
        'Venue' =>  'Text',
        'IsLive'    =>  'Text',
        'UserName'  =>  'Varchar(100)',
        'Phone' =>  'Varchar(30)',
        'EventURL'  =>  'Text',
        'TicketWebsite' =>  'Text',
        'Capacity'  =>  'Int',
        'ExternalImageURL'  =>  'Text',
        'EventTopic' =>  'Text'
    );

    public function canView($member = null) {
        return true;
    }

    public function canEdit($member = null) {
        return true;
    }

    public function canDelete($member = null) {
        return true;
    }

    public function canCreate($member = null) {
        return true;
    }
}



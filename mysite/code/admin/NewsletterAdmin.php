<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14/03/17
 * Time: 2:36 PM
 */
class NewsletterAdmin extends ModelAdmin
{
    /**
     * The current url segment. {@link LeftAndMain::$url_segment}
     *
     * @config
     * @var string
     */
    private static $url_segment = 'newsletters';
    /**
     * The current menu title. {@link LeftAndMain::$menu_title}
     *
     * @config
     * @var string
     */
    private static $menu_title = 'newsletters';
    /**
     * List of all managed {@link DataObject}s in this interface. {@link ModelAdmin::$managed_models}
     *
     * @config
     * @var array|string
     */
    private static $managed_models = array('Newsletter');

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $gridField = $form->Fields()
            ->fieldByName($this->sanitiseClassName($this->modelClass));

        $config = $gridField->getConfig();

        $config->getComponentByType('GridFieldPaginator')->setItemsPerPage(20);
        $config->getComponentByType('GridFieldDataColumns')
            ->setDisplayFields(array(
                'Title'  => 'Title',
                'MailingList'   =>  'MailingList'
            ));

        return $form;
    }
}
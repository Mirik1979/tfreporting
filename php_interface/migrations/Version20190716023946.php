<?php

namespace Sprint\Migration;


class Version20190716023946 extends Version
{

    protected $description = "Ссылка на карточку компании";

    public function up()
    {
        $helper = $this->getHelperManager();

        $helper->UserTypeEntity()->saveUserTypeEntity(array (
            'ENTITY_ID' => 'SONET_GROUP',
            'FIELD_NAME' => 'UF_TIMFORS_COMPANY',
            'USER_TYPE_ID' => 'crm',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array (
                    'LEAD' => 'N',
                    'CONTACT' => 'N',
                    'COMPANY' => 'Y',
                    'DEAL' => 'N',
                    'ORDER' => 'N',
                ),
            'EDIT_FORM_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'Карточка компании ',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'Карточка компании ',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'Карточка компании ',
                ),
            'ERROR_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
        ));
    }

    public function down()
    {
        $helper = $this->getHelperManager();

        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('SONET_GROUP','UF_TIMFORS_COMPANY');
    }

}

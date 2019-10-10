<?php

namespace Sprint\Migration;


class Version20190815013008 extends Version
{

    protected $description = "Стадия сделки. Тип";

    public function up()
    {
        $helper = $this->getHelperManager();

        $helper->UserTypeEntity()->saveUserTypeEntity(array (
            'ENTITY_ID' => 'HLBLOCK_CrmEventDealTimfors',
            'FIELD_NAME' => 'UF_TYPE',
            'USER_TYPE_ID' => 'string',
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
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array (
                    'en' => 'UF_TYPE',
                    'ru' => 'UF_TYPE',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => 'UF_TYPE',
                    'ru' => 'UF_TYPE',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => 'UF_TYPE',
                    'ru' => 'UF_TYPE',
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

        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('HLBLOCK_CrmEventDealTimfors','UF_TYPE');
    }

}

<?php

namespace Sprint\Migration;


class Version20190814030236 extends Version
{

    protected $description = "стадию сделки";

    public function up()
    {
        $helper = $this->getHelperManager();


        $helper->Hlblock()->saveHlblock(array (
            'NAME' => 'CrmEventDealTimfors',
            'TABLE_NAME' => 'crm_event_deal_timfors',
            'LANG' =>
                array (
                    'ru' =>
                        array (
                            'NAME' => 'CrmEventDealTimfors',
                        ),
                    'en' =>
                        array (
                            'NAME' => 'CrmEventDealTimfors',
                        ),
                ),
        ));

        $helper->UserTypeEntity()->saveUserTypeEntity(array (
            'ENTITY_ID' => 'HLBLOCK_CrmEventDealTimfors',
            'FIELD_NAME' => 'UF_TIMELINE_ID',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => 'UF_TIMELINE_ID',
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
                    'MIN_VALUE' => 0,
                    'MAX_VALUE' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array (
                    'en' => 'UF_TIMELINE_ID',
                    'ru' => 'UF_TIMELINE_ID',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => 'UF_TIMELINE_ID',
                    'ru' => 'UF_TIMELINE_ID',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => 'UF_TIMELINE_ID',
                    'ru' => 'UF_TIMELINE_ID',
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
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
            'ENTITY_ID' => 'HLBLOCK_CrmEventDealTimfors',
            'FIELD_NAME' => 'UF_DEAL_ID',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => 'UF_DEAL_ID',
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
                    'MIN_VALUE' => 0,
                    'MAX_VALUE' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array (
                    'en' => 'UF_DEAL_ID',
                    'ru' => 'UF_DEAL_ID',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => 'UF_DEAL_ID',
                    'ru' => 'UF_DEAL_ID',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => 'UF_DEAL_ID',
                    'ru' => 'UF_DEAL_ID',
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
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
            'ENTITY_ID' => 'HLBLOCK_CrmEventDealTimfors',
            'FIELD_NAME' => 'UF_STAGE_NAME',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_STAGE_NAME',
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
                    'en' => 'UF_STAGE_NAME',
                    'ru' => 'UF_STAGE_NAME',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => 'UF_STAGE_NAME',
                    'ru' => 'UF_STAGE_NAME',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => '',
                    'ru' => '',
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
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
            'ENTITY_ID' => 'HLBLOCK_CrmEventDealTimfors',
            'FIELD_NAME' => 'UF_STAGE_ID',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_STAGE_ID',
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
                    'en' => 'UF_STAGE_ID',
                    'ru' => 'UF_STAGE_ID',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => 'UF_STAGE_ID',
                    'ru' => 'UF_STAGE_ID',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => 'UF_STAGE_ID',
                    'ru' => 'UF_STAGE_ID',
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

        $helper->Hlblock()->deleteHlblockIfExists('CrmEventDealTimfors');
    }

}

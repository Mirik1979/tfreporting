<?php

namespace Sprint\Migration;


class Version20190725023200 extends Version
{

    protected $description = "Ресурсный план";

    public function up() {
        $helper = $this->getHelperManager();

        $helper->Iblock()->saveIblockType(array (
            'ID' => 'resource_plan',
            'SECTIONS' => 'N',
            'EDIT_FILE_BEFORE' => '',
            'EDIT_FILE_AFTER' => '',
            'IN_RSS' => 'N',
            'SORT' => '500',
            'LANG' =>
                array (
                    'ru' =>
                        array (
                            'NAME' => 'Ресурсный план',
                            'SECTION_NAME' => '',
                            'ELEMENT_NAME' => '',
                        ),
                    'en' =>
                        array (
                            'NAME' => 'Resource plan',
                            'SECTION_NAME' => '',
                            'ELEMENT_NAME' => '',
                        ),
                ),
        ));

    }

    public function down()
    {
        $helper = $this->getHelperManager();

        $helper->Iblock()->deleteIblockTypeIfExists('resource_plan');
    }

}

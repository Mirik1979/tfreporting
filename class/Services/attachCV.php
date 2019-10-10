<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.08.2019
 * Time: 22:00
 */

namespace local\Services;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;
use CCrmContact;

class attachCV
{

    const GroupId=19;

    private static function getFileName($folder,$name){
        $file_ext = strrchr($name, '.');
        $file_name_old = str_replace($file_ext,'', $name);
        $file_name = $file_name_old;
        $count=0;
        $newName=false;
        while ($newName==false){
            $fileOld = $folder->getChild(
                array(
                    '=NAME' => $file_name.$file_ext,
                    'TYPE' => \Bitrix\Disk\Internals\FileTable::TYPE_FILE
                )
            );
            if ($fileOld){
                $count++;
                $file_name=$file_name_old.'('.$count.')';
            }else{
                $newName=$file_name.$file_ext;
            }
        }
        return $newName;
    }

    public static function setFile(){
        global $USER, $APPLICATION;


        if(!function_exists('__CrmPropductRowListEndResponse'))
        {
            function __CrmPropductRowListEndResponse($result)
            {
                $GLOBALS['APPLICATION']->RestartBuffer();
                header('Content-Type: application/json; charset='.LANG_CHARSET);


                if(!empty($result))
                {
                    echo json_encode($result);
                }
                require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
                die();
            }
        }

        $request = Context::getCurrent()->getRequest();

        if (Loader::includeModule('disk') && Loader::includeModule('crm') && $USER->IsAuthorized() || check_bitrix_sessid() || $request->isPost()){

            $id=(int)$request->getPost("id");

            if($id>0){
                $file = $request->getFile("file");

                $driver = \Bitrix\Disk\Driver::getInstance();
                $storage = $driver->getStorageByGroupId(self::GroupId);
                $folder = $storage->getRootObject();

                $fileName=self::getFileName($folder,$file['name']);

                $newFile=$folder->uploadFile(
                    [
                        "name" => $file['name'],
                        "size" => $file['size'],
                        "tmp_name" => $file['tmp_name'],
                        "type" => $file['type'],
                        "MODULE_ID" => "crm",
                    ],
                    [
                        'CREATED_BY' => $USER->GetID(),
                        'NAME' => $fileName,
                    ]
                );
                if ($newFile)
                {
                    $urlManager = \Bitrix\Disk\Driver::getInstance()->getUrlManager();
                    $url='https://bx.skillscloud.com'.$urlManager->getPathFileDetail($newFile);
                    $CCrmContact=new CCrmContact(false);
                    $arParams=["UF_CRM_1563983113"=>$url];
                    $CCrmContact->Update($id,$arParams);
                }

            }

        }

        __CrmPropductRowListEndResponse(array('sucsess'=>'Y'));

    }

    public static function init(){

        \CJSCore::Init(array("jquery", 'popup'));

        global $APPLICATION;

        $request = Context::getCurrent()->getRequest();
        $rDir  = $request->getRequestedPageDirectory();

        $result = strpos ($rDir, '/crm/contact/details/');
        if ($result !== FALSE){
            $id=(int)str_replace('/crm/contact/details/','',$rDir);
            if($id>0){
                Extension::load('ui.buttons');
                Extension::load('ui.buttons.icons');

                ob_start();
                ?>
                <div class="pagetitle-container">
                    <a href="#" onclick="showPopupAttachCV<?=$id?>(this);return false;" class="webform-small-button">Прикрепить резюме</a>
                </div>
                <script>

                    var AttachCV<?=$id?> = new BX.PopupWindow(
                        "AttachCV<?=$id?>",
                        null,
                        {
                            overlay: {
                                opacity: '80'
                            },
                            titleBar: 'Резюме',
                            content:  '<input type="file" id="attachcv_file<?=$id?>">',
                            closeIcon: {right: "20px", top: "10px" },
                            zIndex: 0,
                            offsetLeft: 0,
                            offsetTop: 0,
                            draggable: {restrict: false},
                            buttons: [
                                new BX.PopupWindowButton({
                                    text: "Прикрепить" ,
                                    className: "" ,
                                    events: {click: function(){
                                            set_attachcv_file<?=$id?>();
                                        }}
                                }),
                                new BX.PopupWindowButton({
                                    text: "Отменить" ,
                                    className: "webform-button-link-cancel" ,
                                    events: {click: function(){
                                            this.popupWindow.close();
                                        }}
                                })
                            ]
                        });

                    var addloader<?=$id?> = new BX.PopupWindow(
                        "my_loader<?=$id?>",
                        null,
                        {
                            overlay: {
                                opacity: '80'
                            },
                            content:  'Ждите',
                            closeIcon: false,
                            zIndex: 0,
                            offsetLeft: 0,
                            offsetTop: 0,
                            draggable: {restrict: false},
                            buttons: [

                            ]
                        });

                    function set_attachcv_file<?=$id?>(){
                        var file_data = $('#attachcv_file<?=$id?>').prop('files');
                        if(file_data.length<=0)
                        {
                            alert('Прикрепите файл.');
                        }else{
                            var form_data = new FormData();
                            form_data.append('file', file_data[0]);
                            form_data.append('id', <?=$id?>);
                            form_data.append('sessid', BX.bitrix_sessid());
                            addloader<?=$id?>.show();
                            AttachCV<?=$id?>.close();
                            $.ajax({
                                url: '/ajax/AttachCV.php',
                                dataType: 'json',
                                cache: false,
                                contentType: false,
                                processData: false,
                                data: form_data,
                                type: 'post',
                                success: function(data){

                                    console.log(data);

                                    addloader<?=$id?>.close();

                                    if(data.error){
                                        var errorloader<?=$id?> = new BX.PopupWindow(
                                            "my_error<?=$id?>",
                                            null,
                                            {
                                                overlay: {
                                                    opacity: '80'
                                                },
                                                content:  data.error,
                                                closeIcon: false,
                                                zIndex: 0,
                                                offsetLeft: 0,
                                                offsetTop: 0,
                                                draggable: {restrict: false},
                                                buttons: [
                                                    new BX.PopupWindowButton({
                                                        text: "Закрыть" ,
                                                        className: "webform-button-link-cancel" ,
                                                        events: {click: function(){
                                                                this.popupWindow.close();
                                                            }}
                                                    })
                                                ]
                                            });
                                        errorloader<?=$id?>.show();
                                    }else{
                                        if(data.sucsess && data.sucsess=="Y"){
                                            location.reload();
                                        }
                                    }
                                }
                            });

                        }
                    }

                    function showPopupAttachCV<?=$id?>(e) {
                        AttachCV<?=$id?>.show();
                        return false;
                    }
                </script>
                <?
                $customHtml = ob_get_clean();

                $APPLICATION->AddViewContent('inside_pagetitle', $customHtml, 0);
            }
        }
    }

}
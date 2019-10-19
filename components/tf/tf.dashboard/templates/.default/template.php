<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CJSCore::Init(array("jquery","date"));
CJSCore::Init(array("jquery"));
\Bitrix\Main\Page\Asset::getInstance()->addJs('/local/components/tf/tf.dashboard/templates/.default/xlsx.full.min.js');
\Bitrix\Main\Page\Asset::getInstance()->addJs('/local/components/tf/tf.dashboard/templates/.default/FileSaver.js');
\Bitrix\Main\Page\Asset::getInstance()->addJs('/local/components/tf/tf.dashboard/templates/.default/Blob.js');
\Bitrix\Main\Page\Asset::getInstance()->addJs('/local/components/tf/tf.dashboard/templates/.default/jhxlsx.js');
//$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/css/style.css');
//require 'vendor/autoload.php';
?>

<div class="db">
<ul class="titles">
    <li class="title active">КПЭ ресурсного центра</li>
    <li class="title">Динамика закрытия вакансий</li>
</ul>
<div class="tabs">
    <div class="tab active">
        <p>Задайте параметры вызова отчета КПЭ ресурcного центра</p>
        <form name="test" id="report" method="" action="">
            <label>Дата начала<input type="text" value="01.10.2019" id="datebegin" name="datebegin" onclick="BX.calendar({node: this, field: this, bTime: false});"></label>
            <label>Дата окончания<input type="text" value="" id="dateend" name="dateend" onclick="BX.calendar({node: this, field: this, bTime: false});"></label>
            <label>Разворачивать по сотрудникам<input type="checkbox" name="detailedpers"/></label>
            <label>Разворачивать по неделям<input type="checkbox" name="detailedweek"/></label>
            <p><input type="submit" value="Сформировать"/></p>
        </form>
        <div id="result"></div>
    </div>
    <div class="tab">
        <!-- <b>.toggle()</b> -->
        <div id="content">
            <p>Задайте параметры вызова списка вакансий для выгрузки в Excel</p>
            <form name="test" id="report1" method="" action="">
                <label>Дата начала<input type="text" value="01.10.2019" id="datebegin" name="datebegin" onclick="BX.calendar({node: this, field: this, bTime: false});"></label>
                <label>Дата окончания<input type="text" value="" id="dateend" name="dateend" onclick="BX.calendar({node: this, field: this, bTime: false});"></label>
                <p><input type="submit" value="Сформировать с выгрузкой в Excel"/></p>
            </form>
            <!-- <button id="button-a">Выгрузить в Excel</button> -->
            <div id="result1"></div>

        </div>
    </div>
</div>
</div>
<script>
    $( document ).ready(function() {
        /*var wb = XLSX.utils.book_new();
        wb.Props = {
            Title: "SheetJS Tutorial",
            Subject: "Test",
            Author: "Red Stapler",
            CreatedDate: new Date(2017,12,19)
        };
        var ws_data = [['hello' , 'world']];  //a row with 2 columns
        var ws = XLSX.utils.aoa_to_sheet(ws_data);
        wb.Sheets["Test Sheet"] = ws;
        var wbout = XLSX.write(wb, {bookType:'xlsx',  type: 'binary'});
        function s2ab(s) {
            var buf = new ArrayBuffer(s.length); //convert s to arrayBuffer
            var view = new Uint8Array(buf);  //create uint8array as viewer
            for (var i=0; i<s.length; i++) view[i] = s.charCodeAt(i) & 0xFF; //convert to octet
            return buf;
        } */
        $("#button-a").click(function(){
            var myTableArray = [];

            $("table#vacancylist tr").each(function() {
                var arrayOfThisRow = [];

                var tableHeader = $(this).find('th');
                if (tableHeader.length > 0) {
                    tableHeader.each(function() {
                        var h={};
                        h.text=$(this).text();
                        //let a = $(this).text();
                        arrayOfThisRow.push(h);
                    });
                    myTableArray.push(arrayOfThisRow);
                }


                var tableData = $(this).find('td');
                if (tableData.length > 0) {
                    tableData.each(function() {
                        var a={};
                        a.text=$(this).text();
                        //let a = $(this).text();
                        arrayOfThisRow.push(a);
                    });
                    myTableArray.push(arrayOfThisRow);
                }
            });

            console.log(myTableArray);
            //let jsonarr = JSON.stringify(myTableArray);
            //console.log(jsonarr);
            var tabulardata2 = [];

            tabulardata2[0] = {
                sheetname: "Sheet1",
                data: myTableArray
            }

            //var tabulardata1={};
            //tabulardata1.sheetName="Sheet1";
            //tabulardata1.data=myTableArray;
            //var tabularData = [{"sheetName":"Sheet1",
            //    "data": [myTableArray
            //       ]}];

            //var tabularData = [{"sheetName":"Sheet1",
            //    "data": [[{"text":"Name"},{"text":"Position"}],[{"text":"Miros"},{"text":"Lyan"}]]
            //}];

            console.log(tabulardata2);

            var options = {
                fileName:"File Name"
            };
            Jhxlsx.export(tabulardata2, options);
        });

        var $tabs = $('.tabs .tab');
        var ACTIVE = 'active';
        var ACTIVE_DOT = '.' + ACTIVE;
        $tabs.not(ACTIVE_DOT).hide();
        // Обработка при клике по <ul>
        $('ul.titles').on('click', 'li:not(ACTIVE_DOT)', function() {
            // Заголовки
            $(this).addClass(ACTIVE).siblings().removeClass(ACTIVE);
            // Тексты
            $tabs.hide().eq($(this).index()).show().addClass(ACTIVE).siblings().removeClass(ACTIVE);
        });


        $("#report").submit(function () {
            event.preventDefault(); // отменяем действие события по умолчанию
            var formData = $(this).serialize(); // создаем переменную, которая содержит закодированный набор элементов формы в виде строки
            console.log(formData);
            $.get("<?echo CUtil::JSEscape($this->GetFolder()."/ajax1.php")?>",
            formData, function (data) { //  передаем и загружаем данные с сервера с помощью HTTP запроса методом GET
                    $("#result").html(data); // вставляем в элемент <div> данные, полученные от сервера
                })
            })

        $("#report1").submit(function () {
            event.preventDefault(); // отменяем действие события по умолчанию
            var formData = $(this).serialize(); // создаем переменную, которая содержит закодированный набор элементов формы в виде строки
            console.log(formData);
            $.get("<?echo CUtil::JSEscape($this->GetFolder()."/ajax2.php")?>",
                formData, function (data) { //  передаем и загружаем данные с сервера с помощью HTTP запроса методом GET
                    $("#result1").html(data); // вставляем в элемент <div> данные, полученные от сервера
                    //var tabularData = [{"sheetName":"Sheet1",
                    //    "data": [[{"text":"Направление"},{"text":"Проект"},{"text":"Статус"},{"text":"Продукт"},
                    //        {"text":"Дата создания"},{"text":"Дата закрытия"},{"text":"Этап сделки"}],
                    //        [{"text":"Направление"},{"text":"iOS2 / Мессенджер от Сбербанка - 1"},{"text":"Открыта"},
                    //            {"text":"iOS-Objective-C,iOS-Swift"},
                    //            {"text":"18.08.2019"},{"text":""},{"text":"Подписан"}]]
                    //}];
                    //console.log(tabularData);
                    //var tabularData = [{"sheetName":"Sheet1",
                    //    "data": [[{"text":"Name"},{"text":"Position"}],[{"text":"Miros"},{"text":"Lyan"}]]
                    //}];


                    //var options = {
                    //    fileName:"VacancyExport",
                    //    maxCellWidth: 100
                    //};
                    //Jhxlsx.export(tabularData, options);
                    var myTableArray = [];

                    $("table#vacancylist tr").each(function() {
                        var arrayOfThisRow = [];

                        var tableHeader = $(this).find('th');
                        if (tableHeader.length > 0) {
                            tableHeader.each(function() {
                                var h={};
                                h.text=$(this).text();
                                //let a = $(this).text();
                                arrayOfThisRow.push(h);
                            });
                            myTableArray.push(arrayOfThisRow);
                        }


                        var tableData = $(this).find('td');
                        if (tableData.length > 0) {
                            tableData.each(function() {
                                var a={};
                                a.text=$(this).text();
                                //let a = $(this).text();
                                arrayOfThisRow.push(a);
                            });
                            myTableArray.push(arrayOfThisRow);
                        }
                    });

                    //console.log(myTableArray);
                    var tabulardata2 = [];

                    tabulardata2[0] = {
                        sheetname: "Sheet1",
                        data: myTableArray
                    };



                    //console.log(tabulardata2);

                    var options = {
                        fileName:"File Name"
                    };
                    Jhxlsx.export(tabulardata2, options);

            })
        })
    });

    var tableToExcel = (function() {
        var uri = 'data:application/vnd.ms-excel;base64,'
            , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head><body><table>{table}</table></body></html>'
            , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
            , format = function(s, c) {
            return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; })
        }
            , downloadURI = function(uri, name) {
            var link = document.createElement("a");
            link.download = name;
            link.href = uri;
            link.click();
        }

        return function(table, name, fileName) {
            if (!table.nodeType) table = document.getElementById(table)
            var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
            var resuri = uri + base64(format(template, ctx))
            downloadURI(resuri, fileName);
        }
    })();


    function export_to_excel(){
        var uri = 'data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,',
            template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" \n\
                                xmlns:x="urn:schemas-microsoft-com:office:excel" \n\
                                xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]>\n\
                                <xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
            base64 = function(s){
                return window.btoa(unescape(encodeURIComponent(s)))
            },
            format = function(s, c){
                return s.replace(/{(\w+)}/g, function(m, p){
                    return c[p];
                })
            }
        var tableExcel = document.getElementById("tableExcel").innerHTML;
        var ctx = {
            worksheet: name || '', table: tableExcel
        };
        var link = document.createElement("a");
        link.download = "export.xls";
        link.href = uri + base64(format(template, ctx))
        link.click();
    }


    /*var addTime = document.getElementById('add');
    addTime.addEventListener('click', getN);
    function getN() {
    var getNum = document.getElementById('datebegin').value;
    console.log(getNum);
    var getNum1 = document.getElementById('dateend').value;
    console.log(getNum1);

    let selected = Array.from(resourceman.options)
        .filter(option => option.selected)
        .map(option => option.value);
    console.log(selected);
    alert(selected);


    let selectedplus = Array.from(recruiter.options)
            .filter(option => option.selected)
            .map(option => option.value);

    console.log(selectedplus);
    alert(selectedplus);
    }*/





    </script>
    <?php
    ?>


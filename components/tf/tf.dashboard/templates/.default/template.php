<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CJSCore::Init(array("jquery","date"));
CJSCore::Init(array("jquery"));
//$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/css/style.css');
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
       Следующий отчет.
    </div>
</div>
</div>
<script>
    $( document ).ready(function() {
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
            });
        });


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


<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator \common\core\swagger\Generator */

$css = <<<css
     .chosen-choices {
        -moz-box-sizing: border-box;
        background-color: #FFFFFF;
        border-radius: 2px;
        cursor: text;
        height: auto !important;
        min-height: 30px;
        overflow: hidden;
        padding: 2px;
        position: relative;
        width: 100%;
    }

    .chosen-choices li {
        float: left;
        list-style: none;
    }

    .chosen-choices li.search-choice {
        background: #f1f1f1;
        border: 1px solid #ededed;
        border-radius: 2px;
        box-shadow: none;
        color: #333333;
        cursor: default;
        line-height: 20px;
        margin: 3px 0 3px 5px;
        padding: 3px 20px 3px 5px;
        position: relative;
    }
    
    .chosen-choices a {
        cursor: pointer;
        text-decoration:none;
    }
    
    .li-pointer {
        cursor: pointer;
        text-decoration:none;
    }
    
    .chosen-choices li.search-choice .search-choice-close {
        position: absolute;
        top: 4px;
        right: 3px;
        display: block;
        width: 12px;
        height: 12px;
        font-size: 1px;
    }
    
    

css;

$this->registerCss($css);
$jsParameterIns = \yii\helpers\Json::encode($generator->parameterIns);
$jsParameterTypes = \yii\helpers\Json::encode($generator->parameterTypes);
$jsDefinitionTypes = \yii\helpers\Json::encode($generator->definitionTypes);
$jsPageTemplate = \yii\helpers\Json::encode($generator->pageTemplate);
$pageTemplate = \yii\helpers\ArrayHelper::getColumn($generator->pageTemplate, 'property');
$jsPageTemplateProperty = \yii\helpers\Json::encode($pageTemplate);
$tags = !empty($generator->tags) ? 1 : 0;
$jsTags = \yii\helpers\Json::encode($generator->tags);
$parameters = !empty($generator->parameters) ? 1 : 0;
$jsParameters = \yii\helpers\Json::encode($generator->parameters);
$responses = !empty($generator->responses) ? 1 : 0;
$jsResponses = \yii\helpers\Json::encode($generator->responses);
$definitions = !empty($generator->definitions) ? 1 : 0;
$jsDefinitions = \yii\helpers\Json::encode($generator->definitions);
$js = <<<js
    window.trTime = 0;
    
    function init() {
        if ({$tags}) {
            let tags = {$jsTags};
            for (let i in tags ) {
                appendTag(tags[i]);
            } 
        }
        if ({$parameters}) {
            let parameters = {$jsParameters};
            initParameter(parameters);
        }else{
            addParameter();
        }
        if ({$responses}) {
            let responses = {$jsResponses};
            initResponse(responses);
        }else{
            addResponse();
        }
        if ({$definitions}) {
            let definitions = {$jsDefinitions};
            for (let d in definitions) {
                let definition = JSON.parse(definitions[d]);
                let name = definition['definitionName'] + '-' + definition['definitionTemplate'];
                appendDefinition(definition, name);
            }
        }
    }
    
    function initParameter(parameters) {
        let names = parameters['parameterName'];
        let fields = ['parameterName', 'parameterDescription', 'parameterIn', 'parameterType', 'ref', 'parameterRequired', 'parameterDefault'];
        let fieldLen = fields.length;
        for (let i in names) {
            let data = {};
            for (let j = 0; j < fieldLen; j++) {
                let v = '';
                let fieldVals = parameters[fields[j]];
                if (fieldVals !== undefined) {
                    let fieldVal = fieldVals[i];
                    v = fieldVal !== undefined ? fieldVal : '';
                }
                data[fields[j]] = v;
            } 
            addParameter(data);
        }
    }
    
    function initResponse(responses) {
        let names = responses['responseStatus'];
        let fields = ['responseStatus', 'responseDescription', 'ref'];
        let fieldLen = fields.length;
        for (let i in names) {
            let data = {};
            for (let j = 0; j < fieldLen; j++) {
                let v = '';
                let fieldVals = responses[fields[j]];
                if (fieldVals !== undefined) {
                    let fieldVal = fieldVals[i];
                    v = fieldVal !== undefined ? fieldVal : '';
                }
                data[fields[j]] = v;
            } 
            addResponse(data);
        }
    }
    
    init();
    
    function appendTag(val) {
        let ul = $('#tag-chosen');
        let form = getForm();
        deleteClass(ul, 'hide');
        let span = $('<span>').text(val);
        let cancel = $("<a>").attr({'class': 'search-choice-close', 'onclick': 'deleteLi(this, "tag-chosen")'}).text('X');
        let tags = $('<input>').attr({'value': val,'type': 'hidden', 'name': form + "[tags][]"});
        let li = $('<li>').attr('class', 'search-choice').append(span).append(tags).append(cancel);
        ul.append(li);
    }
    
    function addTag(){
        let tag = $("#tag");
        if (tag.val()) {
            appendTag(tag.val())
        }
        tag.val('');
    }
    
    function deleteLi(obj, id) {
        let j = $(obj);
        j.parent().remove();
        let ul = $('#' + id);
        if (ul.children().length === 0) {
            addClass(ul, 'hide');
        }
    }
    
    function addClass(obj, cla){
        if (!obj.hasClass(cla)) {
            obj.addClass(cla);
        }
    }
    
    function deleteClass(obj, cla) {
        if (obj.hasClass(cla)) {
            obj.removeClass(cla);
        }
    }
    
    function getForm() {
        return "{$generator->formName()}";
    }
    
    function generateInputName() {
        let name = getForm();
        let len = arguments.length;
        for (let i = 0; i < len; i++) {
            let p = arguments[i];
            if (p === '') {
                continue;
            }
            name += '[' + p + ']';
        }
        return name + '[' + window.trTime + ']';
    }
    
    function generateInput(n, name, type, options) {
        let inputType = type || 'text';
        options = options || {};
        options['class'] = 'form-control';
        options['type'] = inputType;
        if (!options['name']) {
            options['name'] = generateInputName(n, name);
        }
        return $('<input>').attr(options);
    }
    
    function generateSelect(n, name, items, options, val) {
        options = options || {};
        if (!options['name']) {
            options['name'] = generateInputName(n, name);
        }
        options['class'] = 'form-control';
        let selectElement = $('<select>').attr(options);
        for (let item in items) {
            let selectItem = $('<option>').attr('value', item).text(items[item]);
            if (item === val) {
                selectItem.prop('selected', true);
            }
            selectElement.append(selectItem);
        }
        return selectElement;
    }
    
    function generateTd(){
        let len = arguments.length;
        for (let i = 1; i < len; i++) {
            let p = arguments[i];
            let td =  $('<td>').append(p);
            arguments[0].append(td);
        }
        return arguments[0];
    }
    
    function checkIn(obj) {
        let _this = $(obj);
        let v = _this.val();
        if (v === 'body') {
            _this.parent().next().children().prop('disabled', true); 
            _this.parent().next().next().children().prop('disabled', false); 
            _this.parent().next().next().next().next().children().prop('disabled', true); 
        } else {
            _this.parent().next().children().prop('disabled', false); 
            _this.parent().next().next().children().prop('disabled', true); 
            _this.parent().next().next().next().next().children().prop('disabled', false); 
        }
    }
    
    function addParameter(results) {
        window.trTime++;
        let data = results || {};
        let filedVal = data['parameterName'] !== undefined ? data['parameterName'] : '';
        let filed = generateInput('parameters','parameterName', '', {'value': filedVal});
        let descriptionVal = data['parameterDescription'] !== undefined ? data['parameterDescription'] : '';
        let description = generateInput('parameters','parameterDescription', '', {'value': descriptionVal});
        let filedInVal = data['parameterIn'] !== undefined ? data['parameterIn'] : '';
        let filedIn = generateSelect('parameters', 'parameterIn', {$jsParameterIns}, {'onChange': 'checkIn(this)'}, filedInVal);
        let filedTypeVal = data['parameterType'] !== undefined ? data['parameterType'] : '';
        let filedType = generateSelect('parameters','parameterType', {$jsParameterTypes}, '', filedTypeVal);
        let refdVal = data['ref'] !== undefined ? data['ref'] : '';        
        let ref = generateInput('parameters','ref', '', {'value': refdVal});
        let requiredVal = data['parameterRequired'] !== undefined ? data['parameterRequired'] : '';   
        let isRequired  = generateSelect('parameters','parameterRequired', {'true': '是', 'false': '否'}, '', requiredVal);
        let defaultVal = data['parameterDefault'] !== undefined ? data['parameterDefault'] : '';   
        let filedDefault  = generateInput('parameters', 'parameterDefault', '', {'value': defaultVal});
        
        let deleteFiled = $('<a>').attr({'onclick': 'deleteTableRow(this, ".rowParameter")', 'class': 'li-pointer'}).text('删除');
        let tr = $('<tr>').attr('class', 'rowParameter');
        let row = generateTd(tr, filed, description, filedIn, filedType, ref, isRequired, filedDefault, deleteFiled);
        $('#parameterTable').append(row);
        checkIn(filedIn);
    }
    
    function deleteTableRow(obj, name, type) {
        let row = obj || '';
        let t = type || 0;
        if (row) {
            $(row).parent().parent().remove();
        } else {
            if (!t) {
                if (confirm('确定是否删除')) {
                    $('tr').remove(name);
                }
            } else {
                $('tr').remove(name);
            }
        }
        if (name !== undefined && $(name).length <= 0) {
            if (name === '.rowParameter' && !t) {
                addParameter();
            }
            
           if (name === '.rowResponse' && !t) {
                addResponse();
            }
        }
    }
    
    function addResponse(results) {
        window.trTime++;
        let data = results || {};
        let statusVal = data['responseStatus'] !== undefined ? data['responseStatus'] : '';
        let httpStatus = generateInput('responses','responseStatus', '', {'value': statusVal});
        let descriptionVal = data['responseDescription'] !== undefined ? data['responseDescription'] : '';
        let description = generateInput('responses','responseDescription', '', {'value': descriptionVal});
        let refdVal = data['ref'] !== undefined ? data['ref'] : '';        
        let ref = generateInput('responses','ref', '', {'value': refdVal});  
        
        let deleteFiled = $('<a>').attr({'onclick': 'deleteTableRow(this, ".rowResponse")', 'class': 'li-pointer'}).text('删除');
        let tr = $('<tr>').attr('class', 'rowResponse');
        let row = generateTd(tr, httpStatus, description, ref, deleteFiled);
        $('#responseTable').append(row);
    }
    
    function checkTemplate(obj) {
        let _this = $(obj);
        let v = _this.val();
        if (v === 'page') {
            let pages = {$jsPageTemplate};
            for (let p in pages) {
                addDefinition(pages[p]);
            }
        } else {
            deleteTableRow('', '.rowDefinitionPage', 1);
        }
    }
    
    function checkType(obj) {
        let _this = $(obj);
        let v = _this.val();
        if (v === 'object' || v === 'array') {
            _this.parent().next().next().children().prop('disabled', true); 
            _this.parent().next().children().prop('disabled', false); 
        } else {
             _this.parent().next().next().children().prop('disabled', false); 
            _this.parent().next().children().prop('disabled', true); 
        }
    }
    
    function addDefinition(results) {
        window.trTime++;
        let data = results || {};
        let className = '';
        let filedVal = data['property'] !== undefined ? data['property'] : '';
        let filed = generateInput('','', '', {'value': filedVal, 'name':'property' + '[' + window.trTime + ']'});
        let descriptionVal = data['description'] !== undefined ? data['description'] : '';
        let description = generateInput('','', '', {'value': descriptionVal, 'name':'description' + '[' + window.trTime + ']'});
        let filedTypeVal = data['type'] !== undefined ? data['type'] : '';
        let filedType = generateSelect('', '', {$jsDefinitionTypes}, {'onChange': 'checkType(this)', 'name':'type' + '[' + window.trTime + ']'}, filedTypeVal);
        let refdVal = data['ref'] !== undefined ? data['ref'] : '';   
        let ref = generateInput('','', '', {'value': refdVal, 'name':'ref' + '[' + window.trTime + ']'});
        let defaultVal = data['example'] !== undefined ? data['example'] : '';  
        let filedDefault  = generateInput('', '', '', {'value': defaultVal, 'name':'example' + '[' + window.trTime + ']'});
        
        let pageTemplateProperty = {$jsPageTemplateProperty};
        if (pageTemplateProperty.indexOf(filedVal) >= 0) {
           className = ' rowDefinitionPage';
        }
        let deleteFiled = $('<a>').attr({'onclick': 'deleteTableRow(this)', 'class': 'li-pointer'}).text('删除');
        let tr = $('<tr>').attr('class', 'rowDefinition' + className);
        let row = generateTd(tr, filed, description, filedType, ref, filedDefault, deleteFiled);
        $('#definitionTable').append(row);
        checkType(filedType);
    }

    function getNameVal(name) {
        var label = name.match(/\[(.+?)\]/g)[0];
        return label.substr(1, (label.length - 2));
    }
    
    function getName(name) {
        return name.substr(0, name.indexOf('['));
    }
    
    function editDefinition(results, liIndex) {
        let data = results || {};
        let lii = liIndex !== undefined ? liIndex : -1;
        let definitionName = data['definitionName'] !== undefined ? data['definitionName'] : '';
        let definitionNameDiv = generateInput('','', '', {'value': definitionName, 'id': 'definitionName', 'onChange':'checkDefinition()', 'name': 'definitionName', 'data-li': lii});
        $('#definitionNameDiv').html(definitionNameDiv);
        let definitionTemplate = data['definitionTemplate'] !== undefined ? data['definitionTemplate'] : ''; 
        let definitionTemplateDiv = generateSelect('', '', {'default': 'default', 'page': 'page'}, {
            'id': 'definitionTemplate', 
            'name': 'definitionTemplate', 
            'onChange': 'checkTemplate(this)'
        }, definitionTemplate);
        $('#definitionTemplateDiv').html(definitionTemplateDiv);
        deleteTableRow('', '.rowDefinition', 1);
        let definitions = data['data'] !== undefined ? data['data'] : {};
        let definitionNews = {};
        for (let key in definitions) {
            let n = getName(key);
            let v2 = getNameVal(key);
            let vv = {};
            vv[v2] = definitions[key];
            if (definitionNews[n] !== undefined) {
                definitionNews[n].push(definitions[key]);
            } else {
                definitionNews[n] = [definitions[key]];
            }
        }
        let fields = ['property', 'description', 'type', 'ref', 'example'];
        let fieldLen = fields.length;
        let names = definitionNews["property"];
        for (let i in names) {
            let r = {};
            for (let j = 0; j < fieldLen; j++) {
                let v = '';
                let fieldVals = definitionNews[fields[j]];
                if (fieldVals !== undefined) {
                    let fieldVal = fieldVals[i];
                    v = fieldVal !== undefined ? fieldVal : '';
                }
                r[fields[j]] = v;
            } 
            addDefinition(r);
        }
    }
    
    function getInputValue (id) {
        var data = {};
        var radio = [];
        $(':input', '#' + id).each(function (i, input) {
            var o = $(input),
                name = o.attr('name'),
                value = o.val();
            if (o.attr('type') === 'radio') {
                radio.push(name);
            }
            // checked = $('input[name="' + name + '"]:checked');
            if (name === undefined)  {
                return;
            }
            data[name] = value;
        });
        radio = $.unique(radio);
        for (var n = 0; n < radio.length; n++) {
            var checked = $('input[name="' + radio[n] + '"]:checked');
            data[radio[n]] = checked.val() ? checked.val() : '';
        }
        return data;
    }
    
    function appendDefinition(results, name) {
        var ul = $('#definition-chosen');
        deleteClass(ul, 'hide');
        var form = getForm();
        var span = $('<span>').text(name);
        var cancel = $("<a>").attr({'class': 'search-choice-close', 'onclick': 'deleteLi(this, "definition-chosen")'}).text('X');
        var tags = $('<input>').attr({'value': JSON.stringify(results),'type': 'hidden', 'name': form + "[definitions][]"});
        var edit = $('<div>').attr({'onclick': 'showDefinition(this)', 'class': 'li-pointer'}).append(span).append(tags);
        var li = $('<li>').attr({'class': 'search-choice'}).append(edit).append(cancel);
        ul.append(li);
    }
    
    function showDefinition(obj) {
        let _this = $(obj);
        let val = _this.find('input').val();
        let definition = JSON.parse(val);
        let i = -1;
        let lis = $('#definition-chosen li div');
        let count = lis.length;
        for (let j = 0; j < count; j ++) {
            let li = lis[j];
            if (li === obj) {
                i = j;
                break;
            }
        }
        editDefinition(definition, i);
        $('#definitionModal').modal('show');
    }
    
    function validate(obj){
        var reg = /^[0-9]*$/;
        return reg.test(obj);
    }
    
    function updateDefinition() {
        let dName = $('#definitionName');
        let filedVal = dName.val();
        if (!filedVal) {
            alert('自定义名称不能为空');
            return;
        }
        let templateVal = $('#definitionTemplate').val();
        let data = getInputValue('definitionTable');
        let checkStatus = 0;
        for (let d in data) {
            if (getName(d) === 'property') {
                if (data[d] === '') {
                    checkStatus = 1;
                }
                if (validate(data[d])) {
                    checkStatus = 2;
                }
            }
        } 
        if (checkStatus > 0) {
            if (checkStatus === 1) {
                alert('字段不能为空');
            }
            if (checkStatus === 2) {
                alert('字段不能为纯数子');
            }
            return ;
        }
        let results = {
            'definitionName': filedVal,
            'definitionTemplate': templateVal,
            'data': data
        };
        let name = filedVal + '-' + templateVal;
        let liIndex = dName.attr('data-li');
        if (liIndex >= 0) {
            deleteLi($('#definition-chosen li div')[liIndex], "definition-chosen")
        }
        appendDefinition(results, name);
        $('#definitionModal').modal('hide');
    }
    
    function pathInfo(obj){
        let val = $(obj).val();
        let url = '/gii/default/action?id=swagger&name=path&path=' + val;
        $.get(url, function(result) {
            if (!result.has) {
                return ;
            }
            let data  = result.data;
            $('#generator-method').val(data.method);
            $('#tag-chosen li a').each(function(){
                deleteLi($(this), 'tag-chosen');
            });
            for (let i in data.tags ) {
                appendTag(data. tags[i]);
            } 
            let radio = $("#generator-deprecated input[type='radio']");
            radio.prop('checked', false);
            radio.each(function(){
                if ($(this).val() === data.deprecated) {
                    $(this).prop('checked', true);
                }
            });
            let radio2 = $("#generator-security input[type='radio']");
            radio2.prop('checked', false);
            radio2.each(function(){
                if ($(this).val() === data.security) {
                    $(this).prop('checked', true);
                }
            });
            $('#generator-summary').val(data.summary);
            $('#generator-description').val(data.description);
            let checkbox = $("#generator-produces input[type='checkbox']");
            checkbox.prop('checked', false);
            checkbox.each(function(){
                if (data.produces.indexOf($(this).val()) >= 0) {
                    $(this).prop('checked', true);
                }
            });
            let checkbox2 = $("#generator-consumes input[type='checkbox']");
            checkbox2.prop('checked', false);
            checkbox2.each(function(){
                if (data.consumes.indexOf($(this).val()) >= 0) {
                    $(this).prop('checked', true);
                }
            });
            deleteTableRow('', '.rowParameter', 1);
            initParameter(data.parameters);
            deleteTableRow('', '.rowResponse', 1);
            initResponse(data.responses);
            $('#definition-chosen li a').each(function(){
                deleteLi($(this), 'definition-chosen');
            });
            for (let d in data.definitions) {
                let definition = JSON.parse(data.definitions[d]);
                let name = definition['definitionName'] + '-' + definition['definitionTemplate'];
                appendDefinition(definition, name);
            }
        });
    }
    
    function checkDefinition() {
        let _this = $('#definitionName')
        let url = "/gii/default/action?id=swagger&name=definition";

        $.post(url,{definition: _this.val()} , function(result) {
            if (result.has) {
                if (confirm('自定义参数[' + _this.val() + ']已存在，是否需要换一个名称')) {
                    _this.val('');
                }
            }
        });
    }
    
js;
$this->registerJs($js, View::POS_END);

echo $form->field($generator, 'viewPath');
echo $form->field($generator, 'path')->textInput([
    'onChange' => 'pathInfo(this)',
]);
echo $form->field($generator, 'method')->dropDownList($generator->methods, ['prompt' => '请选择']);
echo $form->field($generator, 'tag', [
    'template' => implode("\n", [
        Html::tag('div', '{label}'),
        Html::ul([], ['class' => 'chosen-choices hide', 'id' => 'tag-chosen']),
        Html::tag('div', '{input}', ['class' => 'col-md-10']),
        Html::tag('div', Html::button('添加', ['class' => 'btn btn-success', 'onclick' => 'addTag()']), ['class' => 'col-md-2']),
        '{hint}',
        Html::tag('div', '{error}', ['class' => 'col-md-12']),
    ]),
])->textInput(['id' => 'tag']);
echo $form->field($generator, 'security')->radioList(['true' => '是', 'false' => '否']);
echo $form->field($generator, 'deprecated')->radioList(['true' => '是', 'false' => '否']);
echo $form->field($generator, 'summary');
echo $form->field($generator, 'description')->textarea(['rows' => 5]);
echo $form->field($generator, 'consumes')->checkboxList($generator->contentTypes);
echo $form->field($generator, 'produces')->checkboxList($generator->contentTypes);
echo $form->field($generator, 'parameter', [
    'template' => implode("\n", [
        Html::tag('div', '{label}'),
        Html::tag('table', Html::tag('tr', implode("\n", [
            Html::tag('th', '字段', ['class' => 'text-center']),
            Html::tag('th', '描述', ['class' => 'text-center']),
            Html::tag('th', '参数位置', ['class' => 'text-center']),
            Html::tag('th', '类型', ['class' => 'text-center']),
            Html::tag('th', '引用', ['class' => 'text-center']),
            Html::tag('th', '是否必填', ['class' => 'text-center']),
            Html::tag('th', '默认值', ['class' => 'text-center']),
            Html::tag('th', '操作', ['class' => 'text-center']),
        ])), ['class' => 'table table-striped table-bordered', 'id' => 'parameterTable', 'style' => 'width:90rem']),
        Html::tag('div', implode("\n", [
            Html::button('添加', [
                'class'   => 'btn btn-success',
                'onclick' => 'addParameter()',
            ]),
            Html::button('删除', [
                'class'   => 'btn btn-danger',
                'onclick' => 'deleteTableRow("", ".rowParameter")',
            ]),
        ])),
    ]),
])->hiddenInput();
echo $form->field($generator, 'response', [
    'template' => implode("\n", [
        Html::tag('div', '{label}'),
        Html::tag('table', Html::tag('tr', implode("\n", [
            Html::tag('th', '状态码', ['class' => 'text-center']),
            Html::tag('th', '描述', ['class' => 'text-center']),
            Html::tag('th', '引用', ['class' => 'text-center']),
            Html::tag('th', '操作', ['class' => 'text-center']),
        ])), ['class' => 'table table-striped table-bordered', 'id' => 'responseTable', 'style' => 'width:50rem']),
        Html::tag('div', implode("\n", [
            Html::button('添加', [
                'class'   => 'btn btn-success',
                'onclick' => 'addResponse()',
            ]),
            Html::button('删除', [
                'class'   => 'btn btn-danger',
                'onclick' => 'deleteTableRow("", ".rowResponse")',
            ]),
        ])),
        '{error}',
    ]),
])->hiddenInput();
echo $form->field($generator, 'definition', [
    'template' => implode("\n", [
        Html::tag('div', '{label}'),
        Html::ul([], ['class' => 'chosen-choices hide', 'id' => 'definition-chosen']),
        Html::tag('div', Html::button('添加自定义参数', [
            'class'       => 'btn btn-success',
            'data-toggle' => 'modal',
            'data-target' => '#definitionModal',
            'onclick'     => 'editDefinition()',
        ])),
    ]),
])->hiddenInput();
?>

<div class="modal inmodal" id="definitionModal" tabindex="-1" data-backdrop="static" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="width: 100rem">
        <div class="modal-content animated fadeIn">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">自定义参数设置</h4>
            </div>
            <div class="modal-body">
                <?php
                echo Html::tag('div', implode("\n", [
                    Html::label('名称', 'definitionName', ['class' => 'control-label']),
                    Html::tag('div', '', ['id' => 'definitionNameDiv']),
                ]), ['class' => 'form-group']);
                echo Html::tag('div', implode("\n", [
                    Html::label('模版', 'definitionTemplate', ['class' => 'control-label']),
                    Html::tag('div', '', ['id' => 'definitionTemplateDiv']),
                ]), ['class' => 'form-group']);
                echo Html::tag('table', Html::tag('tr', implode("\n", [
                    Html::tag('th', '字段', ['class' => 'text-center']),
                    Html::tag('th', '描述', ['class' => 'text-center']),
                    Html::tag('th', '类型', ['class' => 'text-center']),
                    Html::tag('th', '引用', ['class' => 'text-center']),
                    Html::tag('th', '默认值', ['class' => 'text-center']),
                    Html::tag('th', '操作', ['class' => 'text-center']),
                ])), ['class' => 'table table-striped table-bordered', 'id' => 'definitionTable', 'style' => 'width:90rem']);
                echo Html::tag('div', implode("\n", [
                    Html::button('添加', [
                        'class'   => 'btn btn-success',
                        'onclick' => 'addDefinition()',
                    ]),
                    Html::button('删除', [
                        'class'   => 'btn btn-danger',
                        'onclick' => 'deleteTableRow("", ".rowDefinition")',
                    ]),
                ]))
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal" id="closeModal">关闭</button>
                <button type="button" class="btn btn-primary" onclick="updateDefinition()">保存</button>
            </div>
        </div>
    </div>
</div>
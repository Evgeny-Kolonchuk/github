<?
$APPLICATION->IncludeFile("/local/include/areas/development.php", array(), array("MODE" => "html", "NAME" => "Данные о разработчике", "TEMPLATE" => 'EMPTY',"SHOW_BORDER" => false));



$this->SetViewTarget('products_detail_text');
	echo $resultDiv;
$this->EndViewTarget();

$APPLICATION->ShowViewContent('products_detail_text');

// Формат даты
FormatDate("j F Y",MakeTimeStamp($arItem['ACTIVE_FROM']))

 // подключение ядра битрикс
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// подключение js библиотек
CJSCore::Init(array('core','jquery', 'fx', 'popup'));

// построение дерева разделов 
foreach ($arMass as $id => $node) {
  if (isset($arMass[$node['parent_id']])) {
    $arMass[$node['parent_id']]['sub'][$id] =& $arMass[$id];
  }
}

// получение символьного кода
$params = Array(
       "max_len" => "100", // обрезает символьный код до 100 символов
       "change_case" => "L", // буквы преобразуются к нижнему регистру
       "replace_space" => "_", // меняем пробелы на нижнее подчеркивание
       "replace_other" => "_", // меняем левые символы на нижнее подчеркивание
       "delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
       "use_google" => "false", // отключаем использование google
    ); 


$code = CUtil::translit("здесь переменная названия элемента", "ru" , $params);


// проверка пароля
function isUserPassword($oldPassword) {
   global $USER;
   if(!$USER->isAuthorized()){
       return false;
   }

   $userId = $USER->GetID();
   $userData = CUser::GetByID($userId)->Fetch();

   $salt = substr($userData['PASSWORD'], 0, (strlen($userData['PASSWORD']) - 32));

   $realPassword = substr($userData['PASSWORD'], -32);

   $oldPassword = md5($salt.$oldPassword);

   return ($oldPassword == $realPassword);
}

// проверка функции mail
echo '<pre>'.print_r((int)mail("kolonchuk@newsite.by", "My Subject", "Line 1\nLine 2\nLine 3") ,true).'</pre>';

// запись в лог
$file = $_SERVER["DOCUMENT_ROOT"]."/log.txt";
fwrite(fopen($file , "a"),date('Y-m-d H:i:s') . ' ' . $text . "\n");


// получение трек номера из объекта заказа
$order = \Bitrix\Sale\Order::load(111);
$shipmentCollection = $order->getShipmentCollection();

foreach ($shipmentCollection as $shipment){
	$track= $shipment->getField("TRACKING_NUMBER");
}

// построение дерева инфоблока
$resultSections = CIBlockSection::GetList(Array(), array("IBLOCK_ID" => $arFields['IBLOCK_ID'], array('LOGIC' => 'OR',array("DEPTH_LEVEL" => 1),array("DEPTH_LEVEL" => 2))));
while( $arSections = $resultSections->Fetch()){

	if(empty($arSections['IBLOCK_SECTION_ID'])){
		$sectionsTree[$arSections['ID']] = array('NAME' => $arSections['NAME'], 'CODE' => $arSections['CODE']);
	}else{
		$sectionsTree[$arSections['IBLOCK_SECTION_ID']]['CHILDS'][] = array('NAME' => $arSections['NAME'],'CODE' => $arSections['CODE'],'ID' => $arSections['ID']);
	}						
}


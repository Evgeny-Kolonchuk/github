<?

use Bitrix\Main\Loader;
Loader::includeModule('iblock');
Loader::includeSharewareModule("intervolga.tips");

if((int)$arResult['ORDER_ID'] > 0){
    $filter = ['filter' => ['=ID' => $arResult['ORDER_ID']]];
    $arResult['ORDER_DATA'] = Bitrix\Sale\Order::getList($filter)->fetch();
}

// выборка D7

use Bitrix\Main\Entity\Query;
use Bitrix\Iblock\ElementTable;
$query = new Query( ElementTable::getEntity() );

        $query
            ->setFilter(['ID' => $arResult['PRODUCT_ID']])
            ->setSelect(['NAME','ID','PREVIEW_PICTURE','DETAIL_PICTURE'])
            ->registerRuntimeField('PRODUCT_CATALOG', array(
                'data_type' => SaleProductTable::getEntity(),
                'reference' => array('=this.ID' => 'ref.ID')
            ))
            ->addSelect('PRODUCT_CATALOG.QUANTITY', 'QUANTITY')
        ;

        $arResult['PRODUCT'] = $query->exec()->fetch();


// подключение файлов
use Bitrix\Main\Page\Asset; 

Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/fix.js"); 
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/styles/fix.css"); 
Asset::getInstance()->addString("<link href='http://fonts.googleapis.com/css?family=PT+Sans:400&subset=cyrillic' rel='stylesheet' type='text/css'>"); 

// Локализация
use Bitrix\Main\Localization\Loc; 

Loc::loadMessages(__FILE__); 
echo Loc::getMessage("INTERVOLGA_TIPS.TITLE"); 


// события
use Bitrix\Main\EventManager; 

$handler = EventManager::getInstance()->addEventHandler( 
    "main", 
    "OnUserLoginExternal", 
    array( 
        "Intervolga\\Test\\EventHandlers\\Main", 
        "onUserLoginExternal" 
    ) 
); 


// Исключения
use Bitrix\Main\SystemException; 

try 
{ 
    // ... 
    throw new SystemException("Error"); 
} 
catch (SystemException $exception) 
{ 
    echo $exception->getMessage(); 
}

// получение пути к шаблону через объект шаблона и подключение скрипта

$template_path = $this->GetFolder();
if(file_exists($_SERVER['DOCUMENT_ROOT'] . $template_path . '/css/telephony.css')){
    Bitrix\Main\Page\Asset::getInstance()->addCss($template_path . '/css/telephony.css');
}



// Отправка почты
use Bitrix\Main\Mail\Event; 
Event::send(array( 
    "EVENT_NAME" => "NEW_USER", 
    "LID" => "s1", 
    "C_FIELDS" => array( 
        "EMAIL" => "info@intervolga.ru", 
        "USER_ID" => 42 
    ), 
));


// Работа с GET и POST параметрами
use Bitrix\Main\Application; 
$request = Application::getInstance()->getContext()->getRequest(); 

$name = $request->getPost("name"); 
$email = htmlspecialchars($request->getQuery("email"));


Получение корзины

$basket = \Bitrix\Sale\Basket::loadItemsForFUser(

   //Получение ID покупателя (НЕ ID пользователя!)
   \Bitrix\Sale\Fuser::getId(),

   //Текущий сайт
   \Bitrix\Main\Context::getCurrent()->getSite()
);

//Обёртка над ORM
$basket = \Bitrix\Sale\Basket::getList($filter);


// работа с заказом

use Bitrix\Sale;
/** int $orderId ID заказа */
$order = Sale\Order::load($order_id);

$order->getId(); // ID заказа
$order->getSiteId(); // ID сайта
$order->getDateInsert(); // объект Bitrix\Main\Type\DateTime
$order->getPersonTypeId(); // ID типа покупателя
$order->getUserId(); // ID пользователя

$order->getPrice(); // Сумма заказа
$order->getDiscountPrice(); // Размер скидки
$order->getDeliveryPrice(); // Стоимость доставки
$order->getSumPaid(); // Оплаченная сумма
$order->getCurrency(); // Валюта заказа

$order->isPaid(); // true, если оплачен
$order->isAllowDelivery(); // true, если разрешена доставка
$order->isShipped(); // true, если отправлен
$order->isCanceled(); // true, если отменен

$order->getField("ORDER_WEIGHT"); // Вес заказа
$order->getField('PRICE'); // Сумма заказа
// Изменение поля:
$order->setField('USER_DESCRIPTION', 'Комментарий к заказу');
$order->save();

$paymentIds = $order->getPaymentSystemId(); // массив id способов оплат
$deliveryIds = $order->getDeliverySystemId(); // массив id способов доставки

$discountData = $order->getDiscount()->getApplyResult();

/** Sale\Basket $basket */
$order->setBasket($basket);

$basket = $order->getBasket();

//Bitrix\Sale\PropertyValue
$propertyCollection = $order->getPropertyCollection();
$ar = $propertyCollection->getArray(); // массив ['properties' => [..], 'groups' => [..] ];
$ar = $propertyCollection->getGroups(); // массив групп
$ar = $propertyCollection->getGroupProperties($groupId); // массив свойств, относящихся к группе

$emailPropValue = $propertyCollection->getUserEmail();
$namePropValue  = $propertyCollection->getPayerName();
$locPropValue   = $propertyCollection->getDeliveryLocation();
$taxLocPropValue = $propertyCollection->getTaxLocation();
$profNamePropVal = $propertyCollection->getProfileName();
$zipPropValue   = $propertyCollection->getDeliveryLocationZip();
$phonePropValue = $propertyCollection->getPhone();
$addrPropValue  = $propertyCollection->getAddress();

$somePropValue = $propertyCollection->getItemByOrderPropertyId($orderPropertyId);
$somePropValue->getValue(); // значение свойства
$somePropValue->getViewHtml(); // представление значения в читаемом виде (напр. для местоположения - путь страна, регион, город)

$arProp  = $somePropValue->getProperty(); // массив данных о самом свойстве
$propId  = $somePropValue->getPropertyId(); // ID свойства
$propName = $somePropValue->getName(); // Название
$isRequired = $somePropValue->isRequired(); // true, если свойство обязательное
$propPerson = $somePropValue->getPersonTypeId(); // Тип плательщика
$propGroup  = $somePropValue->getGroupId(); // ID группы

$somePropValue->setValue("value");
$order->save(); 
// можно $somePropValue->save(), но пересчета заказа не произойдёт

// Список локаций и фильтры по ним
use \Bitrix\Sale\Location\LocationTable;

$db = LocationTable::getList([
                             'filter' => ['>CITY_ID'  => 0, 'SALE_LOCATION_LOCATION_NAME_LANGUAGE_ID' => LANGUAGE_ID, 'SALE_LOCATION_LOCATION_NAME_NAME' => 'Москва'],
                             'select' => ['ID', 'CODE', 'DEPTH_LEVEL', 'PARENT_ID', 'REGION_ID', 'CITY_ID', 'NAME'],
                             'order' => ['SALE_LOCATION_LOCATION_NAME_NAME' => 'asc']
                             ]);


while ($arr = $db->fetch()){
    echo '<pre>'.print_r($arr,true).'</pre>';
}

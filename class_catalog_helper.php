<?
class CCatalogHelper{
	

	public static $iterator = 0;
	

	/*
	 *  установка свойства в заказе
	 *
	 */

	public static function order_prop_update($ar_params, $order_id){

    /*
     *  $ar_params - входящие параметры свойства, которое обновляем
     *  preson_type_id, value, code
     */

    if (CModule::IncludeModule('sale')) {

        if ($ar_property = CSaleOrderProps::GetList([], ['CODE' => $ar_params['code'], 'PERSON_TYPE_ID' => $ar_params['preson_type_id']])->Fetch()) {

            $db = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"), ['ORDER_ID' => $order_id, 'ORDER_PROPS_ID' => $ar_property['ID']]);

            if ($ar_vals = $db->Fetch())	{
                CSaleOrderPropsValue::Update($ar_vals['ID'], ['VALUE' => $ar_params['value']]);
            }else{
                $res = CSaleOrderPropsValue::Add([
                    'NAME' => $ar_property['NAME'],
                    'CODE' => $ar_property['CODE'],
                    'ORDER_PROPS_ID' => $ar_property['ID'],
                    'ORDER_ID' => $order_id,
                    'VALUE' => $ar_params['value'],
                ]);
            }
        }
    }
}

	static function add_order_property($prop_id, $value, $order_id) {

		if(!strlen($prop_id)) {
			return false;
		}

		if (CModule::IncludeModule('sale')) {
			if ($ar_order_props = \CSaleOrderProps::GetByID($prop_id)){

				$db_vals = CSaleOrderPropsValue::GetList([], ['ORDER_ID' => $order_id, 'ORDER_PROPS_ID' => $ar_order_props['ID']]);
				if ($ar_vals = $db_vals->Fetch()) {
					return \CSaleOrderPropsValue::Update($ar_vals['ID'],
						[
							'NAME' => $ar_vals['NAME'],
							'CODE' => $ar_vals['CODE'],
							'ORDER_PROPS_ID' => $ar_vals['ORDER_PROPS_ID'],
							'ORDER_ID' => $ar_vals['ORDER_ID'],
							'VALUE' => $value,
						]
					);
				} else {
					return \CSaleOrderPropsValue::Add(
						[
							'NAME' => $ar_order_props['NAME'],
							'CODE' => $ar_order_props['CODE'],
							'ORDER_PROPS_ID' => $ar_order_props['ID'],
							'ORDER_ID' => $order_id,
							'VALUE' => $value,
						]
					);
				}
			}
		}
	}

	public static function create_translit($field_name, $case = 'L'){

		if(empty($field_name)){
			return false;
		}

		$params = array(
			"max_len" => "100", // обрезает символьный код до 100 символов
			"change_case" => $case, // буквы преобразуются к регистру
			"replace_space" => "_", // меняем пробелы на нижнее подчеркивание
			"replace_other" => "_", // меняем левые символы на нижнее подчеркивание
			"delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
			"use_google" => "false", // отключаем использование google
		);
		return  CUtil::translit($field_name, "ru" , $params);
	}

	public static function create_uf_field($field_name, $essence = 'CRM_DEAL', $user_field_type = 'string'){

		if(empty($field_name) || empty($essence)){
			return false;
		}

		$uf_name = 'UF_'.strtoupper(self::create_translit($field_name));

		if(strlen($uf_name) > 20){
			$uf_name = substr($uf_name, 0, 20);
		}

		$ar_user_field = CUserTypeEntity::GetList( array('ID'=>'ASC'), array('FIELD_NAME' => $uf_name, 'ENTITY_ID' => $essence) )->Fetch();

		if(empty($ar_user_field)){

			$user_type_entity = new CUserTypeEntity;

			$ar_user_field = array(
				'ENTITY_ID' => $essence,
				'FIELD_NAME' => $uf_name,
				'USER_TYPE_ID' => $user_field_type,
				'EDIT_FORM_LABEL' => Array("ru" => $field_name, "en" => $field_name),
				'LIST_COLUMN_LABEL' => Array("ru" => $field_name, "en" => $field_name),
				'LIST_FILTER_LABEL' => Array("ru" => $field_name, "en" => $field_name),
				'EDIT_IN_LIST' => 'N' // запретить редактировать пользователями
			);
			$res = $user_type_entity->Add( $ar_user_field );
		}

		return $ar_user_field;
	}

	static function goods_available_count($ID){

		$quantity = 0;

		if (empty($ID) || !CModule::IncludeModule("catalog")){
			return $quantity;
		}

		$dbStores = \CCatalogStore::GetList(
			array("STORE_ID" => "ASC"),
			array("ACTIVE" => "Y"),
			false,
			false,
			array('ID')
		);
		
		// подтягиваем доступные склады
		while ($arStore = $dbStores->Fetch()){
			$arStoreId[] = $arStore['ID'];
		}

		// собираем доступное количество
		$obStoreProduct = \CCatalogStoreProduct::GetList(
			array("STORE_ID" => "ASC"),
			array("PRODUCT_ID" => $ID,'STORE_ID' => $arStoreId ),
			false,
			false,
			array("ID", "STORE_ID","AMOUNT")
		);

		while ($arStoreProduct = $obStoreProduct->Fetch()) {
			if($arStoreProduct['AMOUNT'] > 0){
				$quantity += $arStoreProduct['AMOUNT'];
			}
		}

		return $quantity;
	}

	public static function get_images($ar_images = []){

		if(!empty($ar_images)){
			$db_files = \CFile::GetList([], [ '@ID' => implode(',', $ar_images) ]);
			$upload_dir = \COption::GetOptionString('main', 'upload_dir', 'upload');
			while ($res = $db_files->fetch()){
				$ar_images[$res['ID']] = "/{$upload_dir}/" . $res['SUBDIR'] . '/' . $res['FILE_NAME'];
			}
		}

		return $ar_images;
	}


    public static function get_images_cache() {

        if (!empty($this->arResult['images'])) {
            $cache_id = md5(serialize($this->arResult['images']));
            $images = [];
            $ob_cache = new \CPHPCache;
            if($this->use_cache && $ob_cache->initCache(($this->cache_time * 30), $cache_id) ){
                $images = $ob_cache->getVars();
            }elseif($ob_cache->startDataCache()){
                $dbl = \CFile::GetList([], ["@ID" => implode(",", $this->arResult['images'])]);
                $upload_dir = \COption::GetOptionString("main", "upload_dir", "upload");
                while ($res = $dbl->fetch()){
                    $images[$res["ID"]] = "/$upload_dir/" . $res['SUBDIR'] . "/" . $res['FILE_NAME'];
                }
                $ob_cache->EndDataCache($images);
            }

        }
        $this->arResult['images'] = $images;
        return $this;
    }
	
}
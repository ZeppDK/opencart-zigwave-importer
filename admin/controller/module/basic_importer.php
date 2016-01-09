<?php
class ControllerModuleBasicImporter extends Controller {
	private $error = array();

	public function index() {

		$data = array();

		$this->load->language('module/basic_importer');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module');		

		// Lets load the currencies. They're necessary.
		$this->load->model('localisation/currency');

		$currencies = $this->model_localisation_currency->getCurrencies();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			// Allowed file extension types
			$allowed = array();

			$catalog_name_filename = $this->request->files['catalog_name']['name'];
			$pricelist_name_filename = $this->request->files['pricelist_name']['name'];

			$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));

			$filetypes = explode("\n", $extension_allowed);

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array(strtolower(substr(strrchr($catalog_name_filename, '.'), 1)), $allowed)) {
				$this->error['error'] = $this->language->get('error_filetype');
			}

			if (!in_array(strtolower(substr(strrchr($pricelist_name_filename, '.'), 1)), $allowed)) {
				$this->error['error'] = $this->language->get('error_filetype');
			}

			// Allowed file mime types
			$allowed = array();

			$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

			$filetypes = explode("\n", $mime_allowed);

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array($this->request->files['catalog_name']['type'], $allowed)) {
				$this->error['error'] = $this->language->get('error_filetype');
			}

			if (!in_array($this->request->files['pricelist_name']['type'], $allowed)) {
				$this->error['error'] = $this->language->get('error_filetype');
			}

			// Check to see if any PHP files are trying to be uploaded
			$catalog_content = file_get_contents($this->request->files['catalog_name']['tmp_name']);
			$pricelist_content = file_get_contents($this->request->files['pricelist_name']['tmp_name']);

			if (preg_match('/\<\?php/i', $catalog_content)) {
				$this->error['error'] = $this->language->get('error_filetype');
			}

			if (preg_match('/\<\?php/i', $pricelist_content)) {
				$this->error['error'] = $this->language->get('error_filetype');
			}

			// pick the right stream processor;
			$catalog_filename_type = $this->request->files['catalog_name']['type'];
			$pricelist_filename_type = $this->request->files['pricelist_name']['type'];
			

			// PRICELIST: Name;SKU-Code;Listprice;personal price
			// CATALOG: Name;SKU-Code;Description

			$pricelist = array();
			$catalog = array();

			// find the source currency, to import from.
			$importcurrencycode = $this->request->post['import_currency'];

			// Make sure, we convert the unit price into the default currency.
			$fromvalue = $currencies[$importcurrencycode]['value'];

			// We need the profit-scaling,
			$profitpercentage = $this->request->post['special_multiplication_factor'];
							
			if ($catalog_filename_type == "text/csv") {
				$Data = str_getcsv($catalog_content, "\n"); //parse the rows 
				foreach($Data as &$Row) {
					$Row = str_getcsv($Row, ","); //parse the items in row
					$catalog []= $Row;
				}			     
			}

			if ($pricelist_filename_type == "text/csv") {
			        $Data = str_getcsv($pricelist_content, "\n"); //parse the rows 
				foreach($Data as &$Row) { 
					$Row = str_getcsv($Row, ","); //parse the items in row
					$pricelist []= $Row;
				}
			}

			$product = array();
			foreach($catalog as $entry) {  
				$name = $entry[0];
				$sku = $entry[1];
				$description = $entry[2];
				$product [$sku] = array ('name' => $name, 
							 'model' => $name,
							 'sku' => $sku, 
							 'product_description' => array ( 1 => array ( $description) ), 
							 'date_added' => time(),
							 'date_modified' => time(),
							 'date_available' => time(),
						   	 'quantity' => 1, 
							 'upc' => 0, 
							 'ean' => 0,
							 'jan' => 0,
							 'isbn' => 0,
							 'mpn' => 0, 
							'location' => "DK",
							'minimum' => 0,
							'subtract' => 0,
							'stock_status_id' => 0,
							'manufacturer_id' => 0,
							'shipping,points' => 0,
							'weight' => 0,
							'weight_class_id' => 0,
							'length' => 0,
							'width' => 0,
							'height' => 0,
							'length_class_id' => 0,
							'status' => 1,
							'tax_class_id' => 0,
							'sort_order' => 0,
							'description' => 0,
							'tag' => 0,
							'meta_title' => 0,
							'meta_description' => 0,
							'meta_keyword' => 0, 
				);
			}

			unset($catalog);

			foreach($pricelist as $entry) {  
				$name = $entry[0];
				$sku = $entry[1];
		
				$personalprice = $entry[3] * $fromvalue;
			
				$product [$sku]['price'] = $personalprice * $profitpercentage;
				
 				$product[$sku]['product_discount'] = array('customer_group_id' => $this->request->post['special_group_id'], 'priority' => 999, 'price' => 					$product[$sku]['price']);

				$product[$sku]['product_special'] = array('customer_group_id' => $this->request->post['discount_group_id'], 'priority' => 999, 'price' => 
				$product [$sku]['price']);

			}

			unset($pricelist);
			
			$this->load->model('catalog/product');

			foreach($product as $item) {
				$test = $this->model_catalog_product->getProduct($item);
				if (!$test) {
				  $this->model_catalog_product->addProduct($item);
				}
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->load->model('customer/customer_group');

		$cgroups = $this->model_customer_customer_group->getCustomerGroups();

		$data['cgroups'] = $cgroups;
			

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_catalog_name'] = $this->language->get('entry_catalog_name');
		$data['entry_pricelist_name'] = $this->language->get('entry_pricelist_name');
		$data['entry_discount_list_price'] = $this->language->get('entry_discount_list_price');
		$data['entry_special_list_price'] = $this->language->get('entry_special_list_price');

		$data['entry_status'] = $this->language->get('entry_status');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('module/basic_importer', 'token=' . $this->session->data['token'], 'SSL')
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('module/basic_importer', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
			);
		}

		$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_extension_module->getModule($this->request->get['module_id']);
		}

		$data['error_warning'] = false;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('module/basic_importer', 'token=' . $this->session->data['token'], 'SSL');
		} else {
			$data['action'] = $this->url->link('module/basic_importer', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
		}

		// GUI error
		if (isset($this->error['catalog_name'])) {
			$data['error_catalog_name'] = $this->error['catalog_name'];
		} else {
			$data['error_catalog_name'] = '';
		}

		if (isset($this->error['pricelist_name'])) {
			$data['error_pricelist_name'] = $this->error['pricelist_name'];
		} else {
			$data['error_pricelist_name'] = '';
		}

		// GUI error
		if (isset($this->error['multiplication_name'])) {
			$data['error_multiplication_name'] = $this->error['multiplication_name'];
		} else {
			$data['error_multiplication_name'] = '';
		}

		if (isset($this->request->post['catalog_name'])) {
			$data['catalogname'] = $this->request->post['catalogname'];
		} elseif (!empty($module_info)) {
			$data['catalogname'] = $module_info['catalogname'];
		} else {
			$data['catalogname'] = '';
		}
	
		if (isset($this->request->post['catalog_name'])) {
			$data['pricelistname'] = $this->request->post['pricelistname'];
		} elseif (!empty($module_info)) {
			$data['pricelistname'] = $module_info['pricelistname'];
		} else {
			$data['pricelistname'] = '';
		}

		$data['currencies_available'] = $currencies;

		$this->response->setOutput($this->load->view('module/basic_importer.tpl', $data));
		
	}

	protected function validate() {
		
		if (!$this->user->hasPermission('modify', 'module/basic_importer')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

 		$catalog_name = html_entity_decode($this->request->files['catalog_name']['name'], ENT_QUOTES, 'UTF-8');
            
            	if ((utf8_strlen($catalog_name) < 3) || (utf8_strlen($catalog_name) > 128)) {
                         $this->error['catalog_name']  = $this->language->get('error_catalog_name');
                }     

		$pricelist_name = html_entity_decode($this->request->files['pricelist_name']['name'], ENT_QUOTES, 'UTF-8');
            
            	if ((utf8_strlen($pricelist_name) < 3) || (utf8_strlen($pricelist_name) > 128)) {
                         $this->error['pricelist_name']  = $this->language->get('error_pricelist_name');
                }        
             		
		$multiplicationfactor = html_entity_decode($this->request->post['special_multiplication_factor'], ENT_QUOTES, 'UTF-8');

                if(!is_numeric($multiplicationfactor)) {
                         $this->error['multiplication_name'] = $this->language->get('error_multiplication_name');
		}
            

		return !$this->error;
	}
}

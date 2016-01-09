<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="upload" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
	<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="upload" class="form-horizontal">
        <div class="form-group">
            <label class="col-sm-2 control-label" for="input-name"><?php echo $entry_catalog_name; ?></label>
            <div class="col-sm-10">
              <input type="file" name="catalog_name" value="<?php echo $catalogname; ?>" placeholder="<?php echo $entry_catalog_name; ?>" id="input-cat" class="form-control" />
              <?php if ($error_catalog_name) { ?>
              <div class="text-danger"><?php echo $error_catalog_name; ?></div>
              <?php } ?>
            </div>
          </div>
	    <div class="form-group">
	    <label class="col-sm-2 control-label" for="input-name"><?php echo $entry_pricelist_name; ?></label>
	    <div class="col-sm-10">
	      <input type="file" name="pricelist_name" value="<?php echo $pricelistname; ?>" placeholder="<?php echo $entry_pricelist_name; ?>" id="input-price" class="form-control" />
	      <?php if ($error_pricelist_name) { ?>
	      <div class="text-danger"><?php echo $error_pricelist_name; ?></div>
	      <?php } ?>
	    </div>
          </div>
 	  <div class="form-group">
            <label class="col-sm-2 control-label" for="input-discount"><?php echo $entry_discount_list_price; ?></label>
            <div class="col-sm-10">
              <select name="discount_group_id" id="input-discount" class="form-control">
                <?php foreach ($cgroups as $group) { ?>
                <?php if ($group['customer_group_id']) { ?>
                <option value="<?php echo $group['customer_group_id']; ?>" selected="selected"><?php echo $group['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $group['customer_group_id']; ?>"><?php echo $group['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
     	  <div class="form-group">
            <label class="col-sm-2 control-label" for="input-banner"><?php echo $entry_special_list_price; ?></label>
            <div class="col-sm-10">
              <select name="special_group_id" id="input-banner" class="form-control">
                <?php foreach ($cgroups as $group) { ?>
                <?php if ($group['customer_group_id']) { ?>
                <option value="<?php echo $group['customer_group_id']; ?>" selected="selected"><?php echo $group['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $group['customer_group_id']; ?>"><?php echo $group['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
	  <div class="form-group">
	    <label class="col-sm-2 control-label" for="import_currency">Source currency</label>
	    <div class="col-sm-10">
	    <select name="special_currency_id" id="import_currency">
	      <?php 
                    foreach ( $currencies_available as $currency ) {
			$tdl = $currency['code'];
     			echo "<option value='$tdl'>$tdl</option>";
		    }
	      ?>
              
	    </select>	
	    </div>
	  </div>
	  <div class="form-group">
	    <label class="col-sm-2 control-label" for="input-banner">Multiplication factor</label>
	    <div class="col-sm-10">
	    <input type="text" name="special_multiplication_factor" required placeholder="Enter a value in the format 0.00"></input>
	    <?php if ($error_multiplication_name) { ?>
            <div class="text-danger"><?php echo $error_multiplication_name; ?></div>
            <?php } ?>
	    </div>
	  </div>
	</form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>

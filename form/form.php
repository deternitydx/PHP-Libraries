<?php

	/**
	 * Form Class File
	 *
	 * Contains Form class
	 * @package Repository
	 * @subpackage Form
	 *
	 * @author Robbie Hott
	 */

Class Form {
	private $html_open;
	private $html_code;
	private $html_close;
	
	private $scripts;
	
	private $title;
	private $description;
	
	private $item_open = "<tr class='form_row'><td class='form_label'><p>";
	private $item_separator = "</p></td><td class='form_input'><p>";
	private $item_close = "</p></td></tr>\n";
	
	private $submit_open = "<tr class='form_row'><td class='form_submit' colspan='2'><p>";
	private $submit_close = "</p></td></tr>\n";
	
	private $months = array('January','February','March','April','May','June','July','August','September','October','November','December');
	
	function __construct($name, $action, $options) {
		$this->html_open = "<form name='$name' action='$action' method='POST' $options><table class='formtable'>\n";
		$this->html_code = "";
		$this->html_close = "</table></form>";
		$this->scripts = array();
	}
	
	public function addSpacing() {
		$this->html_code .= "<tr><td colspan='2'><p>&nbsp;</p></td></tr>\n";
	}
	
	public function addTitle($t) {
		$this->title = "<tr><td colspan='2'><h2>$t</h2></td></tr>";
	}
	
	public function addDescription($d) {
		$this->description = "<tr><td colspan='2' class='form_description'><p>$d</p></td></tr>";
	}
	
	public function addErrorMessage($e) {
		if ($e == 1)
			$this->description .= "<tr><td colspan='2' class='form_error'><p>Please enter all required information.</p></td></tr>";
		if ($e == 2)
			$this->description .= "<tr><td colspan='2' class='form_error'><p>Passwords do not match.</p></td></tr>";
		if ($e == 3)
			$this->description .= "<tr><td colspan='2' class='form_error'><p>You have already created a website with this email address.</p></td></tr>";
	}
	
	public function addHeading($text) {
		//$this->html_code .= "<tr><td colspan='2'><h3>$text</h3></td></tr>\n";
		$this->html_code .= "<tr><td class='form_heading'><h3>$text</h3></td><td></td></tr>\n";
	}
	
	public function addHidden($name, $value) {
		$this->html_open .= "<input type='hidden' name='$name' value='$value' />\n";
	}
	
	public function addScript($type) {
		switch($type) {
			case "color":
				if (!in_array('<script type="text/javascript" src="js/jscolor/jscolor.js"></script>', $this->scripts))
					array_push($this->scripts, '<script type="text/javascript" src="js/jscolor/jscolor.js"></script>');
				break;
		}
	}
	
	public function addItem($type, $name, $description) {
	
		$inner_text = "";
		$javascript = "";
		switch($type) {
			case "radio":
				$options = "";
				$javascript = "";
				if (func_num_args() >= 4)
					$options = func_get_arg(3);
				if (func_num_args() >= 5)
					$javascript = func_get_arg(4);
				$this->html_code .= $this->item_open . "<label for='$name'>$name</label><br><span class='description'>$description</span>" . $this->item_separator;
				foreach($options as $opt => $value)
					$this->html_code .= "<radio name='$name' value='$opt' $javascript/> $value <br />\n";
				$this->html_code .= $this->item_close;
				break;
			case "select":
				$options = "";
				$javascript = "";
				if (func_num_args() >= 4)
					$options = func_get_arg(3);
				if (func_num_args() >= 5)
					$javascript = func_get_arg(4);
				$this->html_code .= $this->item_open . "<label for='$name'>$name</label><br><span class='description'>$description</span>" . $this->item_separator."<select name='$name' $javascript>";
				foreach($options as $opt => $value)
					$this->html_code .= "<option value='$opt'>$value</option>\n";
				$this->html_code .= "</select>" . $this->item_close;
				break;
			case "count":
				$max = 10;
				$javascript = "";
				if (func_num_args() >= 4)
					$max = func_get_arg(3);
				if (func_num_args() >= 5)
					$javascript = func_get_arg(4);
				$this->html_code .= $this->item_open . "<label for='$name'>$name</label><br><span class='description'>$description</span>" . $this->item_separator."<select name='$name' $javascript>";
				for($i = 1; $i <= $max; $i++)
					$this->html_code .= "<option value='$i'>$i</option>\n";
				$this->html_code .= "</select>";
				$this->html_code .= $this->item_close;
				break;
			case "color":
				$this->addScript('color');
				if (func_num_args() >= 4)
					$inner_text = func_get_arg(3);
				if (func_num_args() >= 5)
					$javascript = func_get_arg(4);
				$this->html_code .= $this->item_open . "<label for='$name'>$name</label><br><span class='description'>$description</span>" . $this->item_separator."<input type='text' name='$name"."_display' value='$inner_text' size='3' class=\"color {pickerPosition:'right',pickerFaceColor:'transparent',pickerFace:3,pickerBorder:0,pickerInsetColor:'black',valueElement:'$name'}\" style='border:1px solid black;cursor:pointer' $javascript \><input type='hidden' name='$name' value='12345'\>".$this->item_close;
				break;
			case "birthday":
				$options = "";
				$javascript = "";
				if (func_num_args() >= 4)
					$options = func_get_arg(3);
				if (func_num_args() >= 5)
					$javascript = func_get_arg(4);
				$this->html_code .= $this->item_open . "<label for='$name'>$name</label><br><span class='description'>$description</span>" . $this->item_separator."<select name='$name"."_day' $javascript>";
				for($i = 1; $i <= 31; $i++)
					$this->html_code .= "<option value='$i'>$i</option>\n";
				$this->html_code .= "</select>";
				$this->html_code .= "&nbsp;<select name='$name"."_month' $javascript>";
				foreach($this->months as $month)
					$this->html_code .= "<option value='$month'>$month</option>\n";
				$this->html_code .= "</select>";
				$this->html_code .= "&nbsp;<select name='$name"."_year' $javascript>";
				$year = @date('Y');
				for($i = $year; $i >= $year - 100; $i--)
					$this->html_code .= "<option value='$i'>$i</option>\n";
				$this->html_code .= "</select>";
				
				$this->html_code .= $this->item_close;
				break;
			case "name":
				$options = "";
				$javascript = "";
				if (func_num_args() >= 4)
					$options = func_get_arg(3);
				if (func_num_args() >= 5)
					$javascript = func_get_arg(4);
				$this->html_code .= $this->item_open . "<label for='$name'>$name</label><br><span class='description'>$description</span>" . $this->item_separator."<select name='$name"."_prefix' $javascript>";
				$this->html_code .= "<option value=''></option>\n";
				$this->html_code .= "<option value='Dr.'>Dr.</option>\n";
				$this->html_code .= "<option value='Rev.'>Rev.</option>\n";
				$this->html_code .= "</select>";
				$this->html_code .= "&nbsp;<input type='text' name='$name"."_firstname' size='15' value='First' $javascript onClick=\"this.value=''\">&nbsp;<input type='text' name='$name"."_mi' size='2' value='MI' $javascript onClick=\"this.value=''\">&nbsp;<input type='text' name='$name"."_lastname' size='15' value='Last' $javascript onClick=\"this.value=''\">";
				
				$this->html_code .= $this->item_close;
				break;
			case "phone":
				$inner_text = "";
				$javascript = "";
				if (func_num_args() >= 4)
					$inner_text = func_get_arg(3);
				if (func_num_args() >= 5)
					$javascript = func_get_arg(4);
				$this->html_code .= $this->item_open . "<label for='$name'>$name</label><br><span class='description'>$description</span>" . $this->item_separator."( <input type='text' name='$name"."_area' size='3' maxlength='3' $javascript> ) <input type='text' name='$name"."_first' size='3' maxlength='3' $javascript>-<input type='text' name='$name"."_last' size='4' maxlength='4' $javascript>";
				
				$this->html_code .= $this->item_close;
				break;
			case "address":
				$inner_text = "";
				$javascript = "";
				if (func_num_args() >= 4)
					$inner_text = func_get_arg(3);
				if (func_num_args() >= 5)
					$javascript = func_get_arg(4);
				$this->html_code .= $this->item_open . "<label for='$name'>$name</label><br><span class='description'>$description</span>" . $this->item_separator."<input type='text' name='$name"."_street1' size='25' value='Street' $javascript onClick=\"this.value=''\"><br/><input type='text' name='$name"."_street2' size='25' value='Street' $javascript onClick=\"this.value=''\"><br/><input type='text' name='$name"."_city' size='10' value='City' $javascript onClick=\"this.value=''\"> , <input type='text' name='$name"."_state' size='2' maxlength='2' value='ST' $javascript onClick=\"this.value=''\">  <input type='text' name='$name"."_zip' size='5' maxlength='10' value='Zip' $javascript onClick=\"this.value=''\">";
				
				$this->html_code .= $this->item_close;
				break;
			case "password":
				$inner_text = "";
				$javascript = "";
				if (func_num_args() >= 4)
					$inner_text = func_get_arg(3);
				if (func_num_args() >= 5)
					$javascript = func_get_arg(4);
				$this->html_code .= $this->item_open . "<label for='$name'>$name</label><br><span class='description'>$description</span>" . $this->item_separator."<input type='password' name='$name' value='$inner_text' $javascript \>" . $this->item_close;
				break;
			case "create_password":
				$inner_text = "";
				$javascript = "";
				if (func_num_args() >= 4)
					$inner_text = func_get_arg(3);
				if (func_num_args() >= 5)
					$javascript = func_get_arg(4);
				$this->html_code .= $this->item_open . "<label for='$name'>$name</label><br><span class='description'>$description</span>" . $this->item_separator."<input type='password' name='$name' value='$inner_text' $javascript \>" . $this->item_close;
				$this->html_code .= $this->item_open . "<label for='$name"."2'>$name</label><br><span class='description'>Retype it to make sure you didn't make a typo.</span>" . $this->item_separator."<input type='password' name='$name"."2' value='' $javascript \>" . $this->item_close;
				break;
			case "textbox":
				$inner_text = "";
				$javascript = "";
				$size = array('rows'=>8,'cols'=>45);
				if (func_num_args() >= 4)
					$inner_text = func_get_arg(3);
				if (func_num_args() >= 5)
					$javascript = func_get_arg(4);
				if (func_num_args() >= 6)
					$size = func_get_arg(5);
				$this->html_code .= $this->item_open . "<label for='$name'>$name</label><br><span class='description'>$description</span>" . $this->item_separator."<textarea name='$name' rows='".$size['rows']."' cols='".$size['cols']."' $javascript >$inner_text</textarea>".$this->item_close;
				break;
			case "email":
				$inner_text = "";
				$javascript = "";
				if (func_num_args() >= 4)
					$inner_text = func_get_arg(3);
				if (func_num_args() >= 5)
					$javascript = func_get_arg(4);
				$this->html_code .= $this->item_open . "<label for='$name'>$name</label><br><span class='description'>$description</span>" . $this->item_separator."<input type='text' name='$name' value='$inner_text' $javascript \>".$this->item_close;
				break;
			case "text":
				$inner_text = "";
				$javascript = "";
				$size = 15;
				if (func_num_args() >= 4)
					$inner_text = func_get_arg(3);
				if (func_num_args() >= 5)
					$javascript = func_get_arg(4);
				if (func_num_args() >= 6)
					$size = func_get_arg(5);
				$this->html_code .= $this->item_open . "<label for='$name'>$name</label><br><span class='description'>$description</span>" . $this->item_separator."<input type='text' name='$name' value='$inner_text' size='$size' $javascript \>".$this->item_close;
				break;
			

		}
	}
	
	public function addSubmit($text) {
		$javascript = "";
		if (func_num_args() >= 2)
			$javascript = func_get_arg(2);
		$this->html_code .= $this->submit_open . "<input type='submit' name='Submit' value='$text' $javascript />".$this->submit_close;
	}
	
	public function printForm() {
		echo implode("\n",$this->scripts);
		echo $this->html_open . $this->title . $this->description . $this->html_code . $this->html_close;
	}
	
	public function getHtml() {
		return $html;
	}
}
?>
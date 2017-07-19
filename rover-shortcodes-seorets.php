<?php

class Rover_IDX_Shortcodes_SEORETS
	{
	function __construct() {
		}

	function map_seorets_to_rover($atts)	{

		$translate				= array(
										'type'			=> 'prop_types',
										'property_type'	=> 'PropertySubtype1',
										'baths_full'	=> 'min_baths',
										'bedrooms'		=> 'min_beds',
										'area'			=> 'areas',
										'subdivision'	=> 'subdivision',
										'city'			=> 'cities',
										'county'		=> 'counties',
										'zip'			=> 'zipcode',
										'price'			=> 'min_price',
										'price'			=> 'max_price',
										'price1'		=> 'min_price',
										'price2'		=> 'max_price',

										'waterfront'	=> 'oceanfront',
										
										'elem_school'	=> 'school_elementary',
										'middle_school'	=> 'school_middle',
										'high_school'	=> 'school_high',

										'elem_school'	=> 'school',
										'high_school'	=> 'school',

										'year_built'	=> 'min_year_built',

										'mls_id'		=> 'mlnumber',
										'agent_id'		=> 'listing_agent_mlsid',
										'office_id'		=> 'listing_office_mlsid',

										'perpage'		=> 'items_per_page',
										'order'			=> 'sort_by'
										);

		foreach($translate as $key_from => $key_to)
			{
			if (isset($atts[$key_from]))
				{
				if (in_array($key_from, array('type')))
					{
					$atts['prop_types']	= $this->map_seorets_type($atts[$key_from]);
					}
				else if (in_array($key_from, array('price', 'price1', 'price2')))
					{
					$ret				= $this->map_seorets_price($key_from, $atts[$key_from]);					
					$atts[$ret['key']]	= $ret['val'];			
					}
				else if (in_array($key_from, array('waterfront')))
					{
					$ret				= $this->map_seorets_waterfront($key_from, $atts[$key_from]);					
					$atts[$ret['key']]	= $ret['val'];			
					}
				else if (in_array($key_from, array('order')))
					{
					$atts['sort_by']	= $this->map_seorets_order($atts[$key_from]);			
					}
				else
					{
					$atts[$key_to]		= $atts[$key_from];
					}
	
//				if (strcmp($key_from, $key_to) !== 0)
//					unset($atts[$key_from]);
				}
			}

		return $atts;
		}

	function map_seorets_type($val)		{
		
		$mapped_types					= array();
		foreach (explode(',', $val) as $one_type)
			{
			switch (strtolower(trim($one_type)))
				{
				case 'res':
				case 'homes':
					$mapped_types[]		= 'singlefamily';
					break;
				case 'mtf':
				case 'multifamily':
					$mapped_types[]		= 'multifamily';
					break;
				case 'cnd':
				case 'condos':
					$mapped_types[]		= 'condo';
					break;
				case 'lnds':
				case 'lands':
					$mapped_types[]		= 'land';
					break;
				case 'cms':
				case 'commercial sale':
					$mapped_types[]		= 'commercial';
					break;
				}
			}

		return implode(',', $mapped_types);
		}

	function map_seorets_price($key, $val)	{
		
		if (strcmp($key, 'price') === 0)		//	price="<=:100000"
			$rover_key		= 'max_price';

		if (strcmp($key, 'price1') === 0)		//	price1=">=:100000" price2="<=:200000"
			$rover_key		= 'min_price';

		if (strcmp($key, 'price2') === 0)		//	price1=">=:100000" price2="<=:200000"
			$rover_key		= 'max_price';

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'key is: '.$rover_key.' / val is '.preg_replace('/[^0-9]/', '', $val));

		return array(
					'key'	=> $rover_key,
					'val'	=> preg_replace('/[^0-9]/', '', $val)
					);
		}

	function map_seorets_waterfront($key, $val)	{
		
		if (strcasecmp($val, 'Gulf') === 0)		//	waterfront="Gulf"
			$rover_key		= 'gulffront';

		if (strcasecmp($val, 'Bay') === 0)		//	waterfront="Bay"
			$rover_key		= 'bayfront';

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'key is: '.$rover_key.' / val is 1');

		return array(
					'key'	=> $rover_key,
					'val'	=> 1
					);
		}

	function map_seorets_order($val)		{

		//	order="price:DESC"
		//	order="date_created:DESC"

		$mapped_types					= array();
		$two_parts 						= explode(':', $val);

		switch (strtolower($two_parts[0]))
			{
			case 'price':
				return (strcasecmp($two_parts[1], 'DESC') === 0)
							? 'SortPrice'
							: 'SortPriceAsc';

			case 'date_created':
				return 'SortNewest';
			}

		return implode(',', $mapped_types);
		}

	}
?>
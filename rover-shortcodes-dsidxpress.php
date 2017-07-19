<?php

class Rover_IDX_Shortcodes_DS
	{
	function __construct() {
		}

	function map_dsidxpress_to_rover($atts)	{

		$translate				= array(
										'propertytypes'	=> 'prop_types',
										'city'			=> 'cities',
										'county'		=> 'counties',
										'zip'			=> 'zipcode',
										'minprice'		=> 'min_price',
										'maxprice'		=> 'max_price',

										'minbeds'		=> 'min_beds',
										'maxbeds'		=> 'max_beds',

										'minbaths'		=> 'min_baths',
										'maxbaths'		=> 'max_baths',

										'minyear'		=> 'min_year_built',
										'maxyear'		=> 'max_year_built',

										'minimpsqft'	=> 'sqft',
										'minlotsqft'	=> 'acres',

										'orderby'		=> 'sort_by'
										);

		foreach($translate as $key_from => $key_to)
			{
			if (isset($atts[$key_from]))
				{
				if (in_array($key_from, array('type')))
					{
					$atts['prop_types']	= $this->map_dsidxpress_type($atts[$key_from]);
					}
				else if (in_array($key_from, array('minprice')))
					{
					$atts[$key_to]		= preg_replace('/[^0-9]/', '', $atts[$key_from]);			
					}
				else if (in_array($key_from, array('maxprice')))
					{
					$atts['maxprice']	= preg_replace('/[^0-9]/', '', $atts[$key_from]);			
					}
				else if (in_array($key_from, array('minlotsqft')))
					{
					$atts['acres']		= (intval($atts[$key_from]) > 0)
												? round(intval($atts[$key_from]) / 43560, 2)
												: 0;		
					}
				else if (in_array($key_from, array('orderby')))
					{
					$atts['sort_by']	= $this->map_dsidxpress_order($atts['orderby'], $atts['orderdir']);			
					}
				else
					{
					$atts[$key_to]		= $atts[$key_from];
					}
	
//				if (strcmp($key_from, $key_to) !== 0)
//					unset($atts[$key_from]);
				}
			}
		}

	function map_dsidxpress_type($val)		{
		
		$mapped_types					= array();
		foreach (explode(',', $val) as $one_type)
			{
			switch (strtolower(trim($one_type)))
				{
				case '511':
					$mapped_types[]		= 'condo';
					break;
				case '512':
					$mapped_types[]		= 'townhouse';
					break;
				case '513':
					$mapped_types[]		= 'condo';
					$mapped_types[]		= 'singlefamily';
					$mapped_types[]		= 'townhouse';
					break;
				case '516':
					$mapped_types[]		= 'singlefamily';
					break;
				case '517':
					$mapped_types[]		= 'fractional';
					break;
				case '805':
					$mapped_types[]		= 'land';
					break;
				case '806':
					$mapped_types[]		= 'mobilehome';
					break;
				case '807':
					$mapped_types[]		= 'resincome';
					break;

				case '541':
				case '542':
				case '543':
				case '544':
				case '545':
				case '546':
				case '547':
					$mapped_types[]		= 'rental';
					break;
				}
			}

		return implode(',', $mapped_types);
		}

	function map_dsidxpress_order($val, $dir)		{

		//	&idx-d-SortOrders<0>-Column=Price
		//	&idx-d-SortOrders<0>-Direction=ASC

		$mapped_types					= array();

		switch (strtolower($val))
			{
			case 'Price':
				return (strcasecmp($dir, 'DESC') === 0)
							? 'SortPrice'
							: 'SortPriceAsc';

			case 'ImprovedSqFt':
				return 'sortsqft';

			case 'LotSqFt':
				return 'sortacres';

			case 'DateAdded':
				return 'SortNewest';
			}

		return implode(',', $mapped_types);
		}


	}
?>
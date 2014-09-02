<?php
class Needs_Highlight
{
	/**
	 * searches for a keyword and returns highlighted matches
	 * 
	 * @param string $fullText
	 * @param string $searchText
	 * @param string $color
	 * 
	 * @return string
	 */
	public static function textHighLight($fullText,$searchText,$color = "#FF0000")
	{
		$result = $fullText;
		
		//search term length has to be at least 2 characters
		if(strlen($searchText) > 1)
		{
			$searchText = str_replace("/", "\/", $searchText);
			//added "i" at the end for case-insensitive search
			preg_match_all("/$searchText+/i", $result, $matches);

			if(isset($matches))
			{
				if(is_array($matches[0]) && count($matches[0]) >= 1) 
				{
					 foreach ($matches[0] as $match)
					{
					   $result = str_replace($match, '<span style="color:'.$color.';">'.$match.'</span>', $result);
					}			
				}
			}

			return $result;
		}
		else
		{
			
		}
	}
}

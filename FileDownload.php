<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2008
 * @author     Andreas Schempp <andreas@schempp.ch
 * @license    LGPL
 */


/**
 * Backend field for frontend field "file upload"
 * Allows to download the uploaded file
 */
class FileDownload extends Widget
{
	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = false;
	
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';
	
	
	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$strFile = $this->value;
		
		if (is_array($strFile) && count($strFile))
		{
			$strZip = 'system/html/'.md5(serialize($strFile)).'.zip';
		
			if (!file_exists(TL_ROOT . '/' . $strZip) || is_dir(TL_ROOT . '/' . $strFile))
			{
                // Count number of files to know if we have any
				$count = 0;
				
				$objZip = new ZipWriter($strZip);
				
				foreach( $strFile as $file )
				{
				    if (file_exists(TL_ROOT . '/' . $file) || is_dir(TL_ROOT . '/' . $strFile))
				    {
				        $count++;
				        $objZip->addFile($file, basename($file));
				    }
				}
				
				// No files in zip archive. Remove it!
				if ($count == 0)
				{
				    $objZip->close();
				    unlink(TL_ROOT . '/' . $strZip);
				    return $GLOBALS['TL_LANG']['MSC']['nodownload'];
				}
				
				$objZip->close();
			}
			
			$strFile = $strZip;
		}
		
		if (!file_exists(TL_ROOT . '/' . $strFile) || is_dir(TL_ROOT . '/' . $strFile))
		{
            return $GLOBALS['TL_LANG']['MSC']['nodownload'];
		}
		
		return sprintf('<a href="%s" onclick="window.open(this.href); return false;" id="%s" class="tl_upload%s">%s</a>', 
				$strFile,
				$this->strId,
				(strlen($this->strClass) ? ' ' . $this->strClass : ''), 
				$GLOBALS['TL_LANG']['MSC']['download']);
	}
}
<?php
/*=========================================================
// File:        index.php
// Description: redirection file of checkdomains
// Created:     2020-03-02
// Licence:     GPL-3.0-or-later
// Copyright 2020 Michel Dubois

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
=========================================================*/


	$temp = explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_NAME']);
	$url = '';
	for ($i=0; $i<=array_search('checkdomains', $temp); $i++) {
		$url .= $temp[$i].DIRECTORY_SEPARATOR;
	}

	header('Location: '.$url.'checkdomains.php');
?>

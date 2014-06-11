<?php

abstract class UploadErrors
{

	const WRONG_EXTENSION = 1;
	const FILE_IS_TOO_BIG = 2;
	const TARGET_DIRECTORY_NOT_WRITABLE = 3;
	const UNKNOWN_ERROR = 4;

}
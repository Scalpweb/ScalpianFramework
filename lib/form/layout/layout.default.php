<?php

echo OrionTools::linef($this->openTag());
echo OrionTools::linef($this->tokenFieldTag(), 1);

foreach ($this->getFields() as $field)
	echo OrionTools::linef($field->getHtml(), 1);

echo OrionTools::linef($this->closeTag());
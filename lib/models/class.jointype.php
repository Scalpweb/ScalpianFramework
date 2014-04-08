<?php

abstract class JoinType
{
    const LEFT_JOIN                 = 'LEFT JOIN';
    const RIGHT_JOIN                = 'RIGHT JOIN';
    const INNER_JOIN                = 'INNER JOIN';
    const STRAIGHT_JOIN             = 'STRAIGHT_JOIN';
    const LEFT_OUTER_JOIN           = 'LEFT OUTER JOIN';
    const RIGHT_OUTER_JOIN          = 'RIGHT OUTER JOIN';
    const NATURAL_LEFT_JOIN         = 'NATURAL LEFT JOIN';
    const NATURAL_LEFT_OUTER_JOIN   = 'NATURAL LEFT OUTER JOIN';
    const NATURAL_RIGHT_JOIN        = 'NATURAL RIGHT JOIN';
    const NATURAL_RIGHT_OUTER_JOIN  = 'NATURAL RIGHT OUTER JOIN';
}
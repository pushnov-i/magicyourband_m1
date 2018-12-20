<?php
/*
 * PHP Debug Library
 * 
 * @author Victor Vikont vikont707@gmail.com
 */


// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =  dump
function vd($data, $ret=false) { return Vic::dump($data, $ret); }

function vdc($key, $data=null, $ret=false)
{
	if(1 == func_num_args()) {
		Vic::register($key);
	} else {
		return Vic::registry($key) ? Vic::dump($data, $ret) : '';
	}
}

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = vdA
function vdA($data, $ret=false, $explodeArrayTo=false)
{
	return Vic::dumpA($data, $ret, $explodeArrayTo);
}

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = trace
function trace($level=0) { print Vic::trace($level); }
function strace($level=0) { return Vic::trace($level); }


function sql($sql, $ret=false) { return Vic::printSQL($sql, $ret); }


// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * Vic
class Vic
{
	const MAX_ARRAY_DUMP_DEPTH = 10;

	protected static $_scriptsPrinted = false;
	protected static $_SQLScriptsPrinted = false;

	protected static $_registry = array();


	public static function register($key, $value = true)
	{
		self::$_registry[$key] = $value;
	}

	public static function unregister($key)
	{
		unset(self::$_registry[$key]);
	}

	public static function registry($key)
	{
		return isset(self::$_registry[$key]) ? self::$_registry[$key] : null;
	}


	// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =  replacePairs
	public static function replacePairs($s, $openingSym, $closingSym, $replaceOpening, $replaceClosing)
	{
		$startPos = 0;
		$endPos = 0;
		while(false !== $startPos=strpos($s, $openingSym, $endPos))
		{
			if(false === $endPos=strpos($s, $closingSym, $startPos+1)) break;
			$s = substr($s, 0, $startPos).$replaceOpening.substr($s, $startPos+strlen($openingSym));
			$endPos = $endPos + strlen($replaceOpening) - strlen($openingSym);
			$s = substr($s, 0, $endPos).$replaceClosing.substr($s, $endPos+strlen($closingSym));
			$endPos = $endPos + strlen($replaceClosing) - strlen($closingSym) + 1;
		}
		return $s;
	}



	public static function printScripts()
	{
		if(!self::$_scriptsPrinted) {
			self::$_scriptsPrinted = true;

			$scripts = <<<VIC
<style type="text/css">
	.vic {clear:both;overflow:hidden;color:#000;background:none #fff;}
	.vic div {display:block !important;float:none !important;text-align:left !important;}
	.vic span {display:inline !important;float:none !important;}
	.vic .tab {overflow:hidden; padding-left:40px; border-left:1px dotted Silver;}
	.vic .collapsed {height:1.3em; border-bottom:solid 1px red; overflow:hidden;}
	.vic .item-name {color:red !important;}
	.vic .item {color:black !important;}
	.vic .value {font-weight:bold;}
	.vic .boolean {color:#75507b;}
	.vic .null {color:#3465a4;}
	.vic .string {color:#cc0000;}
	.vic .length {font-style:italic;}
	.vic .class {color:gray;}
	.vic .type {color:brown;}
	.vic .object {color:#0077ff;}
	.vic .resource {color:#007700;}
	.vic .warning {color:red; font-weight:bold;}
	.vic .follow {color:#888a85;}
	.vic .wordWrap {white-space:normal; word-wrap:break-word;}
</style>
<script type="text/javascript">
Vic = {
	lightOn: function(element){
		element.parentNode.style.backgroundColor = "silver";
		return false;
	},
	lightOff: function(element){
		element.parentNode.style.backgroundColor = "transparent";
		return false;
	},
	collapseToggle: function(element){
		if(element.parentNode.collapsed) {
			element.parentNode.classList.remove("collapsed");
			element.parentNode.collapsed = 0;
		} else {
			element.parentNode.classList.add("collapsed");
			element.parentNode.collapsed = 1;
		}
		return false;
	},
	toggleClassName: function(element, cName) {
		var cn = ' ' + element.className + ' ';
		if(cn.indexOf(' ' + cName + ' ') > -1) {
			element.className = cn.replace(' ' + cName + ' ', ' ').trim();
		} else {
			element.className = cName + (element.className ? ' ' + element.className : '');
		}
	}
}
</script>
VIC;
			return $scripts;
		}
		return '';
	}


	public static function dump($data, $ret=false)
	{
		$res = '';
		$res .= self::printScripts();
		$res .= '<pre class="vic">'.self::_dump($data, $ret).'</pre>';

		if($ret) return $res;
		else echo $res;
	}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - dumpA
	public static function dumpA($data, $ret=false, $explodeArrayTo=false)
	{
		return self::_dump($data, $ret, 0, false, $explodeArrayTo);
	}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - print_value
	private static function _print_array($value, $level=0, $explodeArrayTo=false)
	{
		if(		($level > self::MAX_ARRAY_DUMP_DEPTH)
			||	($explodeArrayTo && $level>$explodeArrayTo)
		) {
			return '<span class="warning">The level of the array is too deep&nbsp;!</span>';
		}

		$res = '';
		foreach($value as $k => $v)
			$res .= self::_dump($v, true, $level, $k, $explodeArrayTo);
		return $res;
	}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - _dump
	private static function _dump($data, $ret=false, $level=0, $name=false, $explodeArrayTo=false)
	{
		$type = gettype($data);

		$tab = $level ? ' class="tab"' : '';

		$res = '<div'.$tab.'>';
		if(func_num_args()>3) {
			$name = func_get_arg(3);
			$res .= self::_printSimpleVar($name, false).' <span class="follow">=&gt;</span> ';
		}
		if('NULL' != $type) {
			$res .= '<span class="type">'.$type.'</span> ';
		}

		switch($type) {
			case 'array':
				$res .= '<span onmouseover="Vic.lightOn(this)" onmouseout="Vic.lightOff(this)" onclick="Vic.collapseToggle(this)"><i>[size='.(int)count($data).']</i> (</span><br />'
					.self::_print_array($data, $level+1, $explodeArrayTo)
					.'<span class="clearer" onmouseover="Vic.lightOn(this)" onmouseout="Vic.lightOff(this)" onclick="Vic.collapseToggle(this)">) <i>end of'.($name?' <font color="#006600">'.$name.'</font>':'').' array</i></span>';
				break;

			case 'object':
				$res .= '<font color="#007700">'.get_class($data).'</font> <span onmouseover="Vic.lightOn(this)" onmouseout="Vic.lightOff(this)" onclick="Vic.collapseToggle(this)">(</span><br />'
//					.self::_print_array((array)$data, $level+1, $explodeArrayTo)
					. gettype($data)
					.'<span class="clearer" onmouseover="Vic.lightOn(this)" onmouseout="Vic.lightOff(this)" onclick="Vic.collapseToggle(this)">) <i>end of'.($name?' <font color="#006600">'.$name.'</font>':'').' object</i></span>';
				break;

			case 'resource':
				$res .= get_resource_type($data);
				break;

			default:
				$res .= self::_printSimpleVar($data);
		}
		$res .= '</div>';

		return $res;
	}



	private static function _printSimpleVar($data, $details = true)
	{
		$res = '';
		switch(gettype($data))
		{
			case 'NULL'     : $res .= '<span class="null">null</span>'; break;
			case 'boolean'  : $res .= '<span class="boolean">'.($data ? 'true' : 'false').'</span>'; break;
			case 'integer'  : $res .= '<span class="integer">'.(int)$data.'</span>'; break;
			case 'double'   : $res .= '<span class="double">'.(double)$data.'</span>'; break;
			case 'string'   : $res .= '<span class="string" onclick="Vic.toggleClassName(this, \'wordWrap\')">\''.htmlspecialchars($data).'\'</span>' . ($details ? ' <span class="length">length='.strlen($data).'</span>' : ''); break;
		}
		return $res;
	}



	public static function trace($level=0)
	{
		$trace = debug_backtrace();
		array_shift($trace);
		array_shift($trace);

		// $res = '<div style="text-align:left">';
		$res = self::printScripts();

		$res .= '<pre class="vic">';

		$counter = 0;

		foreach($trace as $item)
		{
			if($level > 0) {
				if($counter < $level)
					$counter++;
				else
					break;
			} elseif($level < 0) {
				$counter++;
				if($counter <= count($trace) + $level)
					continue;
			}

			$res .= '<p>';
			$res .= (isset($item['file']) ? ($item['file'].' : '.$item['line']) : (print_r($item, true))).'<br />';

			if(isset($item['class']))   $res .= '<span class="class">'.$item['class'].'</span> ';
			if(isset($item['object']))  $res .= get_class($item['object']);
			if(isset($item['type']))    $res .= ' <span class="type">'.htmlspecialchars($item['type']).'</span> ';

			$res .= $item['function'].'(';
			if(count($item['args']))
			{
				// $res .= self::_dump($item['args']);
				$res .= ' ';
				foreach($item['args'] as $aName => $aValue)
				{
					if(gettype($aValue) == 'array')
						$res .= '<span class="array">Array</span>';
					elseif(gettype($aValue) == 'object')
						$res .= '<span class="object">'.get_class($aValue).'</span>';
					elseif(gettype($aValue) == 'resource')
						$res .= '<span class="resource">Resource '.get_resource_type($aValue).'</span>';
					else $res .= self::_printSimpleVar($aValue);

					$res .= ', ';
				}
			}

			$res .= ')';
			$res .= '</p>';
		}
		$res .= '</pre>';
		// $res .= '</div>';
		return $res;
	}



	public static function printSQL($sql, $ret=false)
	{
		if($sql instanceof Zend_Db_Select) $sql = $sql->assemble();
		elseif( $sql instanceof Mage_Core_Model_Mysql4_Collection_Abstract
			||  $sql instanceof Mage_Eav_Model_Entity_Collection_Abstract
		) $sql = $sql->getSelect()->assemble();

	// TODO: ��������� �� ������� ����������� � ������ (������ ��� "������-����� ��������", ��� ������� �������� �� ������)
	// TODO: ������������ ������ ������
	// TODO: ��������� ������� � WHERE

		$keywords = array('ACCESSIBLE', 'ADD', 'ALL', 'ALTER', 'ANALYZE', 'AND', 'AS', 'ASC', 'ASENSITIVE', 'BEFORE', 'BETWEEN', 'BIGINT', 'BINARY', 'BLOB', 'BOTH', 'BY', 'CALL', 'CASCADE', 'CASE', 'CHANGE', 'CHAR', 'CHARACTER', 'CHECK', 'COLLATE', 'COLUMN', 'CONDITION', 'CONSTRAINT', 'CONTINUE', 'CONVERT', 'CREATE', 'CROSS', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'CURRENT_USER', 'CURSOR', 'DATABASE', 'DATABASES', 'DAY_HOUR', 'DAY_MICROSECOND', 'DAY_MINUTE', 'DAY_SECOND', 'DEC', 'DECIMAL', 'DECLARE', 'DEFAULT', 'DELAYED', 'DELETE', 'DESC', 'DESCRIBE', 'DETERMINISTIC', 'DISTINCT', 'DISTINCTROW', 'DIV', 'DOUBLE', 'DROP', 'DUAL', 'EACH', 'ELSE', 'ELSEIF', 'ENCLOSED', 'ESCAPED', 'EXISTS', 'EXIT', 'EXPLAIN', 'FALSE', 'FETCH', 'FLOAT', 'FLOAT4', 'FLOAT8', 'FOR', 'FORCE', 'FOREIGN', 'FROM', 'FULLTEXT', 'GRANT', 'GROUP', 'HAVING', 'HIGH_PRIORITY', 'HOUR_MICROSECOND', 'HOUR_MINUTE', 'HOUR_SECOND', 'IF', 'IGNORE', 'IN', 'INDEX', 'INFILE', 'INNER', 'INOUT', 'INSENSITIVE', 'INSERT', 'INT', 'INT1', 'INT2', 'INT3', 'INT4', 'INT8', 'INTEGER', 'INTERVAL', 'INTO', 'IS', 'ITERATE', 'JOIN', 'KEY', 'KEYS', 'KILL', 'LEADING', 'LEAVE', 'LEFT', 'LIKE', 'LIMIT', 'LINEAR', 'LINES', 'LOAD', 'LOCALTIME', 'LOCALTIMESTAMP', 'LOCK', 'LONG', 'LONGBLOB', 'LONGTEXT', 'LOOP', 'LOW_PRIORITY', 'MASTER_SSL_VERIFY_SERVER_CERT', 'MATCH', 'MEDIUMBLOB', 'MEDIUMINT', 'MEDIUMTEXT', 'MIDDLEINT', 'MINUTE_MICROSECOND', 'MINUTE_SECOND', 'MOD', 'MODIFIES', 'NATURAL', 'NOT', 'NO_WRITE_TO_BINLOG', 'NULL', 'NUMERIC', 'ON', 'OPTIMIZE', 'OPTION', 'OPTIONALLY', 'OR', 'ORDER', 'OUT', 'OUTER', 'OUTFILE', 'PRECISION', 'PRIMARY', 'PROCEDURE', 'PURGE', 'RANGE', 'READ', 'READS', 'READ_ONLY', 'READ_WRITE', 'REAL', 'REFERENCES', 'REGEXP', 'RELEASE', 'RENAME', 'REPEAT', 'REPLACE', 'REQUIRE', 'RESTRICT', 'RETURN', 'REVOKE', 'RIGHT', 'RLIKE', 'SCHEMA', 'SCHEMAS', 'SECOND_MICROSECOND', 'SELECT', 'SENSITIVE', 'SEPARATOR', 'SET', 'SHOW', 'SMALLINT', 'SPATIAL', 'SPECIFIC', 'SQL', 'SQLEXCEPTION', 'SQLSTATE', 'SQLWARNING', 'SQL_BIG_RESULT', 'SQL_CALC_FOUND_ROWS', 'SQL_SMALL_RESULT', 'SSL', 'STARTING', 'STRAIGHT_JOIN', 'TABLE', 'TERMINATED', 'THEN', 'TINYBLOB', 'TINYINT', 'TINYTEXT', 'TO', 'TRAILING', 'TRIGGER', 'TRUE', 'UNDO', 'UNION', 'UNIQUE', 'UNLOCK', 'UNSIGNED', 'UPDATE', 'USAGE', 'USE', 'USING', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP', 'VALUES', 'VARBINARY', 'VARCHAR', 'VARCHARACTER', 'VARYING', 'WHEN', 'WHERE', 'WHILE', 'WITH', 'WRITE', 'XOR', 'YEAR_MONTH', 'ZEROFILL');

		$newLineKeywords = array('SELECT', 'FROM', 'WHERE', 'HAVING', 'ORDER', 'GROUP', 'LEFT', 'LIMIT', 'UNION', 'INNER JOIN', 'OUTER JOIN', 'SET');

		$functions = array(
	'AVG', 'BIT_AND', 'BIT_OR', 'BIT_XOR', 'COUNT', 'GROUP_CONCAT', 'MAX', 'MIN', 'STD', 'STDDEV_POP', 'STDDEV_SAMP', 'STDDEV', 'SUM', 'VAR_POP', 'VAR_SAMP', 'VARIANCE',

	'IF', 'IFNULL', 'NULLIF',

	'ASCII', 'BIN', 'BIT_LENGTH', 'CHAR_LENGTH', 'CHAR', 'CHARACTER_LENGTH', 'CONCAT_WS', 'CONCAT', 'ELT', 'EXPORT_SET', 'FIELD', 'FIND_IN_SET', 'FORMAT', 'HEX', 'INSERT', 'INSTR', 'LCASE', 'LEFT', 'LENGTH', 'LIKE', 'LOAD_FILE', 'LOCATE', 'LOWER', 'LPAD', 'LTRIM', 'MAKE_SET', 'MATCH', 'MID', 'NOT LIKE', 'NOT REGEXP', 'OCTET_LENGTH', 'ORD', 'POSITION', 'QUOTE', 'REGEXP', 'REPEAT', 'REPLACE', 'REVERSE', 'RIGHT', 'RLIKE', 'RPAD', 'RTRIM', 'SOUNDEX', 'SOUNDS LIKE', 'SPACE', 'STRCMP', 'SUBSTR', 'SUBSTRING_INDEX', 'SUBSTRING', 'TRIM','UCASE', 'UNHEX', 'UPPER',

	'AES_DECRYPT', 'AES_ENCRYPT', 'BENCHMARK', 'BIT_COUNT', /*'&', '~', '|', '^',*/ 'CHARSET', 'COERCIBILITY', 'COLLATION', 'COMPRESS', 'CONNECTION_ID', 'CURRENT_USER', 'DATABASE', 'DECODE', 'DEFAULT', 'DES_DECRYPT', 'DES_ENCRYPT', 'ENCODE', 'ENCRYPT', 'FOUND_ROWS', 'GET_LOCK', 'INET_ATON', 'INET_NTOA', 'IS_FREE_LOCK', 'IS_USED_LOCK', 'LAST_INSERT_ID', '<<', 'MASTER_POS_WAIT', 'MD5', 'NAME_CONST', 'OLD_PASSWORD', 'PASSWORD', 'RAND', 'RELEASE_LOCK', '>>', 'ROW_COUNT', 'SCHEMA', 'SESSION_USER', 'SHA1','SHA', 'SLEEP', 'SYSTEM_USER', 'UNCOMPRESS', 'UNCOMPRESSED_LENGTH', 'USER', 'UUID', 'VALUES','VERSION',

	'ABS', 'ACOS', 'ASIN', 'ATAN2', 'ATAN', 'ATAN', 'CEIL', 'CEILING', 'CONV', 'COS',  'COT', 'CRC32', 'DEGREES', 'DIV', 'EXP', 'FLOOR', 'LN', 'LOG10', 'LOG2', 'LOG', 'MOD', 'OCT', 'PI', 'POW', 'POWER', 'RADIANS', 'RAND', 'ROUND', 'SIGN', 'SIN', 'SQRT', 'TAN', /*'*',*/ 'TRUNCATE',

	'ADDDATE', 'ADDTIME', 'CONVERT_TZ', 'CURDATE', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'CURTIME', 'DATE_ADD', 'DATE_FORMAT', 'DATE_SUB', 'DATE', 'DATEDIFF', 'DAY', 'DAYNAME', 'DAYOFMONTH', 'DAYOFWEEK', 'DAYOFYEAR', 'EXTRACT', 'FROM_DAYS', 'FROM_UNIXTIME', 'GET_FORMAT', 'HOUR', 'LAST_DAY','LOCALTIME', 'LOCALTIMESTAMP', 'MAKEDATE', 'MAKETIME', 'MICROSECOND', 'MINUTE', 'MONTH', 'MONTHNAME', 'NOW', 'PERIOD_ADD', 'PERIOD_DIFF', 'QUARTER', 'SEC_TO_TIME', 'SECOND',  'STR_TO_DATE', 'SUBDATE', 'SUBTIME', 'SYSDATE', 'TIME_FORMAT', 'TIME_TO_SEC', 'TIME', 'TIMEDIFF', 'TIMESTAMP', 'TIMESTAMPADD', 'TIMESTAMPDIFF', 'TO_DAYS',  'UNIX_TIMESTAMP', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP', 'WEEK', 'WEEKDAY', 'WEEKOFYEAR', 'YEAR', 'YEARWEEK',
	);

		$signs = array('=', '&lt;', '&gt;', '!', ':', '#', '--', '&amp;', '/', );
		$comparisonOperatorComplexSigns = array('&lt;  =', '&gt;  =', '&lt;  &gt;', '&lt;  &lt;', '&gt;  &gt;', '&lt;  =  &gt;', '!  =', ':  =', '&amp;  &amp;', );
		$operators = array('=', '&lt;', '&gt;', '&lt;&lt;', '&gt;&gt;', '&lt;=&gt;', '&lt;&gt;', '!=', '~', '|', '^', '&amp;', '*', '/', '-', '+', '%', '!');

		$sql = ' '.htmlspecialchars($sql).' ';
		$sql = str_replace("\n", ' <br /> ', $sql);
		$sql = str_replace('(', '( ', $sql);
		$sql = str_replace(')', ' ) ', $sql);

		// foreach($operators as $k) // operators
		foreach($signs as $k) // operator signs
			$sql = str_replace($k, ' '.$k.' ', $sql);

		$sql = str_replace('< / ', '</', $sql); // restoring closing tags
		$sql = str_replace(' / >', '/>', $sql); // restoring closing tags
		$sql = str_replace('/ *', '/*', $sql); // restoring closing tags
		$sql = str_replace('* /', '*/', $sql); // restoring closing tags

		foreach($comparisonOperatorComplexSigns as $k) // restoring complex operator symbols
			$sql = str_replace($k, str_replace('  ', '', $k), $sql);

		foreach($operators as $k) // operators
			$sql = str_replace(' '.$k.' ', ' <span class="operator">'.$k.'</span> ', $sql);

		foreach($functions as $k) // functions
		{
			$sql = str_replace(' '.$k.'(', ' <span class="function">'.$k.'</span>(', $sql);
			$sql = str_replace(' '.strtolower($k).'(', ' <span class="function">'.$k.'</span>(', $sql); // making it upper-case
		}

		foreach($newLineKeywords as $k)
		{
			$sql = str_replace(' '.$k.' ', ' <br /> '.$k.' ', $sql);
			$sql = str_replace(' '.strtolower($k).' ', ' <br /> '.$k.' ', $sql);
		}

		$sql = str_replace('<br />  <br />', '<br />', $sql);
		$sql = str_replace('<br /> <br />', '<br />', $sql);

		foreach($keywords as $k)
		{
			$sql = str_replace(' '.$k.' ', ' <b><span class="keyword">'.$k.'</span></b> ', $sql);
			$sql = str_replace(' '.strtolower($k).' ', ' <b><span class="keyword">'.$k.'</span></b> ', $sql);
		}

		$sql = self::replacePairs($sql, ' \'', '\' ', ' <span class="string">\'', "'</span> ");
		$sql = self::replacePairs($sql, ' "', '" ', ' <span class="string">"', '"</span> ');
		$sql = self::replacePairs($sql, ' @', ' ', ' <span class="variable">@', '</span> ');
		$sql = self::replacePairs($sql, '`', '`', '<span class="name">`', '`</span>');
		$sql = self::replacePairs($sql, ' # ', '<br />', ' <font color="comment"># ', '</span><br />');
		$sql = self::replacePairs($sql, ' -- ', '<br />', ' <font color="comment">-- ', '</span><br />');
		$sql = self::replacePairs($sql, '/*', '*/', '<span class="comment">/*', '*/</span>');

		$sql .= '<br />';

		if($ret) {
			return $sql;
		} else {
			if(!self::$_SQLScriptsPrinted) {
				self::$_SQLScriptsPrinted = true;
				echo '
	<style>
	.vic {clear:both;overflow:hidden;color:#000;background:none #fff;padding:10px;text-align:left}
	.vic.SQL {}
	.vic.SQL .keyword {color:black;}
	.vic.SQL .string {color:red;}
	.vic.SQL .variable {color:magenta;}
	.vic.SQL .name {color:green;}
	.vic.SQL .comment {color:#808000;}
	.vic.SQL .function {color:blue;font-style:italic}
	.vic.SQL .operator {color:fuchsia;}
	</style>';
			}
			echo '<div class="vic SQL">'.$sql.'</div>';
		}
	}


}
<?php
/**
 * SQL Log Panel Element
 *
 * PHP versions 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org
 * @package       debug_kit
 * @subpackage    debug_kit.views.elements
 * @since         DebugKit 0.1
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
$headers = array('Query', 'Affected', 'Num. rows', 'Took (ms)', 'Actions');
if (isset($debugKitInHistoryMode)) {
	$content = $this->Toolbar->readCache('sql_log', $this->request->params['pass'][0]);
}
?>
<h2><?php echo __d('debug_kit', 'Sql Logs')?></h2>
<?php if (!empty($content)) : ?>
	<?php foreach ($content['connections'] as $dbName => $explain): ?>
	<div class="sql-log-panel-query-log">
		<h4><?php echo $dbName ?></h4>
		<?php
			if (!isset($debugKitInHistoryMode)):
				$queryLog = $this->Toolbar->getQueryLogs($dbName, array(
					'explain' => $explain, 'threshold' => $content['threshold']
				));
			else:
				$queryLog = $content[$dbName];
			endif;
			echo '<h5>';
			echo __d(
				'debug_kit', 
				'Total Time: %s ms <br />Total Queries: %s queries', 
				$queryLog['time'],
				$queryLog['count']
			);
			echo '</h5>';
			echo $this->Toolbar->table($queryLog['queries'], $headers, array('title' => 'SQL Log ' . $dbName));
		 ?>
		<h4><?php echo __d('debug_kit', 'Query Explain:'); ?></h4>
		<div id="sql-log-explain-<?php echo $dbName ?>">
			<a id="debug-kit-explain-<?php echo $dbName ?>"> </a>
			<p><?php echo __d('debug_kit', 'Click an "Explain" link above, to see the query explanation.'); ?></p>
		</div>
	</div>
	<?php endforeach; ?>
<?php else:
	echo $this->Toolbar->message('Warning', __d('debug_kit', 'No active database connections'));
endif; ?>

<script type="text/javascript">
//<![CDATA[
DEBUGKIT.module('sqlLog');
DEBUGKIT.sqlLog = function () {
	var $ = DEBUGKIT.$;

	return {
		init : function () {
			var sqlPanel = $('#sql_log-tab');
			var buttons = sqlPanel.find('input');

			// Button handling code for explain links.
			// performs XHR request to get explain query.
			var handleButton = function (event) {
				event.preventDefault();
				var form = $(this.form),
					data = form.serialize(),
					dbName = form.find('input[name*=ds]').val() || 'default';

				var fetch = $.ajax({
					url: this.form.action,
					data: data,
					type: 'POST',
					success : function (response) {
						$('#sql-log-explain-' + dbName).html(response);
					},
					error : function () {
						alert('Could not fetch EXPLAIN for query.');
					}
				});
			};
	
			buttons.filter('.sql-explain-link').on('click', handleButton);
		}
	};
}();
DEBUGKIT.loader.register(DEBUGKIT.sqlLog);
//]]>
</script>

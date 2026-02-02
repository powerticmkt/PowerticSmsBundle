/**
 * PowerticSms Integration JavaScript
 * Handles showing/hiding fields based on transport type selection
 */
Mautic.powerticSmsOnLoad = function () {
	// Find the transport type selector
	var transportField = mQuery('#integration_details_apiKeys_transport_type');

	if (transportField.length) {
		// Initial toggle on page load - execute immediately
		Mautic.togglePowerticSmsFields(transportField.val());

		// Add change listener
		transportField.off('change.powerticsms').on('change.powerticsms', function () {
			Mautic.togglePowerticSmsFields(mQuery(this).val());
		});
	}
};

/**
 * Toggle visibility of HTTP vs RabbitMQ configuration fields
 * @param {string} transportType - 'http' or 'rabbitmq'
 */
Mautic.togglePowerticSmsFields = function (transportType) {
	var httpFields = [
		'#integration_details_apiKeys_url',
		'#integration_details_apiKeys_apikey'
	];

	var rabbitmqFields = [
		'#integration_details_apiKeys_rabbitmq_host',
		'#integration_details_apiKeys_rabbitmq_port',
		'#integration_details_apiKeys_rabbitmq_user',
		'#integration_details_apiKeys_rabbitmq_password',
		'#integration_details_apiKeys_rabbitmq_vhost',
		'#integration_details_apiKeys_rabbitmq_queue',
		'#integration_details_apiKeys_rabbitmq_exchange',
		'#integration_details_apiKeys_rabbitmq_routing_key'
	];

	if (transportType === 'http') {
		// Show HTTP fields, hide RabbitMQ fields
		httpFields.forEach(function (selector) {
			mQuery(selector).closest('.mb-3, .row, .form-group').show();
		});
		rabbitmqFields.forEach(function (selector) {
			mQuery(selector).closest('.mb-3, .row, .form-group').hide();
		});
	} else if (transportType === 'rabbitmq') {
		// Hide HTTP fields, show RabbitMQ fields
		httpFields.forEach(function (selector) {
			mQuery(selector).closest('.mb-3, .row, .form-group').hide();
		});
		rabbitmqFields.forEach(function (selector) {
			mQuery(selector).closest('.mb-3, .row, .form-group').show();
		});
	}
};

// Initialize when document is ready
mQuery(document).ready(function () {
	Mautic.powerticSmsOnLoad();
});

// Hook into Mautic's AJAX content loading (when modal opens)
mQuery(document).on('shown.bs.modal', '#IntegrationEditModal', function () {
	// Small delay to ensure form is fully rendered
	setTimeout(function () {
		Mautic.powerticSmsOnLoad();
	}, 100);
});

// Also listen for any AJAX complete that might load the integration form
mQuery(document).ajaxComplete(function (event, xhr, settings) {
	// Check if this is an integration-related AJAX call
	if (settings.url && settings.url.indexOf('integration') !== -1) {
		setTimeout(function () {
			Mautic.powerticSmsOnLoad();
		}, 150);
	}
});

// Fallback: Watch for DOM changes in case modal content is dynamically loaded
if (typeof MutationObserver !== 'undefined') {
	var powerticObserver = new MutationObserver(function (mutations) {
		mutations.forEach(function (mutation) {
			if (mutation.addedNodes.length) {
				var transportField = mQuery('#integration_details_apiKeys_transport_type');
				if (transportField.length && !transportField.data('powerticsms-initialized')) {
					transportField.data('powerticsms-initialized', true);
					Mautic.powerticSmsOnLoad();
				}
			}
		});
	});

	// Start observing when document is ready
	mQuery(document).ready(function () {
		var targetNode = document.body;
		if (targetNode) {
			powerticObserver.observe(targetNode, {
				childList: true,
				subtree: true
			});
		}
	});
}

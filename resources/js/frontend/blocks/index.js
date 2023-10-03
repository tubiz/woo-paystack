/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';

import { PAYMENT_METHOD_NAME } from './constants';
const settings = getSetting( 'paystack_data', {} );

const defaultLabel = __(
	'Paystack ',
	'woo-paystack'
);

const label = decodeEntities( settings.title ) || defaultLabel;
/**
 * Content component
 */
const Content = () => {
	return decodeEntities( settings.description || '' );
};

const Label = () => {
	return (
		<>
			<span>
				{ label }
				<img
					src={ settings.logo_url }
					alt={ label }
				/>
			</span>
		</>
	);
};

/**
 * Paystack payment method config object.
 */
const Paystack = {
	name: PAYMENT_METHOD_NAME,
	label: <Label />,
	content: <Content />,
	edit: <Content />,
	canMakePayment: () => true,
	ariaLabel: label,
	supports: {
		showSavedCards: settings.allow_saved_cards,
		showSaveOption: settings.allow_saved_cards,
		features: settings.supports,
	},
};

registerPaymentMethod( Paystack );

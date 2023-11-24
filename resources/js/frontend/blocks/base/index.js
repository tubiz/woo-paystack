/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';

const defaultLabel = __(
    'Paystack ',
    'woo-paystack'
);

export const ariaLabel = ({ title }) => {
    return decodeEntities( title ) || defaultLabel;
}

/**
 * Content component
 */
export const Content = ({ description }) => {
    return decodeEntities( description || '' );
};

const PaymentIcons = ({ logoUrls, label }) => {
    return (
        <div style={{ display: 'flex', flexDirection: 'row', gap: '0.5rem', flexWrap: 'wrap' }}>
            {logoUrls.map((logoUrl, index) => (
                <img key={index} src={logoUrl} alt={label} />
            ))}
        </div>
    );
};

export const Label = ({ logoUrls, title }) => {
    return (
        <>
            <div style={{ display: 'flex', flexDirection: 'row', gap: '0.5rem' }}>
                <div>
                    { ariaLabel( { title: title } ) }
                </div>
                <PaymentIcons logoUrls={ logoUrls } label={ ariaLabel( { title: title } ) } />
            </div>
        </>
    );
};

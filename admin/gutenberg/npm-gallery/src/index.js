//  Import CSS.
import './editor.scss';
import './style.scss';

const {Component} = wp.element;
import {SelectGalerie} from './components/galerieTypes';
import {
    InspectorControls,
    ColorPaletteControl
} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';
const {registerBlockType} = wp.blocks;
import {
    TextControl,
    PanelBody,
    Panel,
} from '@wordpress/components';
const psGalleryIcon = createElement('svg',
    {
        width: 20,
        height: 20
    },
    createElement( 'path',
        {
            d: "M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2zM14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1zM2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1h-10z"

        }
    )
);
registerBlockType('hupa/post-selector-two-galerie', {
    title: __('Post selector 2 gallery','post-selector'),
    icon: psGalleryIcon,
    class:'ps2-gallery',
    category: 'media',
    attributes: {
        selectedGalerie: {
            type: 'string',
        },
        valWidgetInput: {
            type: 'string',
        },
        hoverBGColor: {
            type: 'string',
            default: ''
        },
        TextColor: {
            type: 'string',
            default: ''
        },
    },
    keywords: [
        __(' Gutenberg Galerie BY Jens Wiecker'),
        __('Gutenberg POST Selector Galerie'),
    ],

    edit: class extends Component {
        constructor(props) {
            super(...arguments);
            this.props = props;
            this.updateSelectedGalerie = this.updateSelectedGalerie.bind(this);
            this.onOverWidgetInputChange = this.onOverWidgetInputChange.bind(this);

            this.onChangeBGColor = this.onChangeBGColor.bind(this);
            this.onChangeTextColor = this.onChangeTextColor.bind(this);
        }

        updateSelectedGalerie(selectedGalerie) {
            this.props.setAttributes({selectedGalerie});
        }

        onOverWidgetInputChange(valWidgetInput) {
            this.props.setAttributes({valWidgetInput});
        }

        onChangeBGColor(hoverBGColor) {
            this.props.setAttributes({hoverBGColor});
        }

        onChangeTextColor(TextColor) {
            this.props.setAttributes({TextColor});
        }

        render() {

            const SmallLine = ({color}) => (
                <hr
                    className="hr-small-trenner"
                />
            );
            const {overWidgetInput, attributes: {valWidgetInput = ''} = {}} = this.props;
            const {inputBGColor, attributes: {hoverBGColor = ''} = {}} = this.props;
            const {inputTextColor, attributes: {TextColor = ''} = {}} = this.props;
            return (
                <div className="wp-block-hupa-post-selector-galerie">
                    <InspectorControls>
                        <div id="hupa-posts-controls">
                            <Panel>
                                <PanelBody
                                    className="hupa-body-sidebar"
                                    title={__("Settings", 'post-selector')}
                                    initialOpen={true}
                                >
                                    <TextControl className= {overWidgetInput}
                                                 label={__("Headline for widget:", 'post-selector')}
                                                 value={valWidgetInput}
                                                 onChange={this.onOverWidgetInputChange}
                                                 type="text"
                                                 help={__("Relevant for Gutenberg widgets only.", 'post-selector')}

                                    />

                                </PanelBody>
                            </Panel>
                            <Panel>
                                <PanelBody
                                    className="hupa-body-sidebar"
                                    title={__("Colors for hover box", 'post-selector')}
                                    initialOpen={false}
                                >
                                    <div className="sidebar-input-headline">
                                        {__("Hover text color", 'post-selector')}
                                    </div>
                                    <div className={inputTextColor}>
                                        <ColorPaletteControl
                                            onChange={this.onChangeTextColor}
                                            value={TextColor}
                                        />
                                    </div>

                                    <div className="sidebar-input-headline">
                                        {__('Hover background color', 'post-selector')}
                                    </div>
                                    <div className={inputBGColor}>
                                        <ColorPaletteControl
                                            onChange={this.onChangeBGColor}
                                            value={hoverBGColor}
                                        />
                                    </div>
                                </PanelBody>
                            </Panel>
                        </div>
                    </InspectorControls>
                    <Panel className="galerie-form-panel">
                        <h5 className="galerie-headline">
                            {__('Post selector 2 gallery ', 'post-selector')}
                        </h5>
                        <SmallLine/>
                        <SelectGalerie
                            /* TODO JOB select Galerie */
                            selectedGalerie={this.props.attributes.selectedGalerie}
                            updateSelectedGalerie={this.updateSelectedGalerie}
                        />
                    </Panel>
                </div>
            );
        }
    },
    save() {
        return null;
    },
});
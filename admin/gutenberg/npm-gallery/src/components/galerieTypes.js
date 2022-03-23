/**
 * Gutenberg POST SELECTOR
 * @package Hummelt & Partner WordPress Theme
 * Copyright 2021, Jens Wiecker
 * https://www.hummelt-werbeagentur.de/
 */

const {Component} = wp.element;
import axios from 'axios';
import {__} from '@wordpress/i18n';
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
export class SelectGalerie extends Component {
    constructor(props) {
        super(...arguments);
        this.props = props;
        this.state = {
            selectGalerie: [],
        }
        this.galerieSelectChange = this.galerieSelectChange.bind(this);
    }

    componentDidMount() {
        axios.get(PS2RestObj.url + 'get-galerie-data', {
            headers: {
                'content-type': 'application/json',
                'X-WP-Nonce': PS2RestObj.nonce
            }
        })
            .then(({data = {}} = {}) => {
                this.setState({
                    selectGalerie: data.select,

                });
            });
    }

    galerieSelectChange(e) {
        this.props.updateSelectedGalerie(
            this.props.selectedGalerie = e
        );
    }

    render() {
        return (
            <div>
                <div className="settings-form-flex-column">
                    <label className="form-label" htmlFor="GalerieSelect">{psGalleryIcon} <b className="b-fett">{__('Gallery', 'post-selector')}</b>&nbsp;{__('select', 'post-selector')}: </label>
                    <select className="form-select" name="options" id="GalerieSelect"
                            onChange={e => this.galerieSelectChange(e.target.value)}>
                        <option value=""> {__('select', 'post-selector')} ...</option>
                        {!this.state.selectGalerie ? (
                            <option value="">{__('loading', 'post-selector')}</option>) : (this.state.selectGalerie).map((select, index) =>
                            <option
                                key={index} value={select.id}
                                selected={select.id == this.props.selectedGalerie}>{select.name}</option>)}
                    </select>
                </div>
            </div>
        );
    }
}
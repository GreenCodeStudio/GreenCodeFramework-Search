import {pageManager} from "../../../Core/js/pageManager";

export class index {
    constructor(page, data) {
        page.querySelector('form').onsubmit = e => {
            e.preventDefault();
            pageManager.goto("/Search/index/" + encodeURIComponent(page.querySelector('form input').value));
        }
    }
}
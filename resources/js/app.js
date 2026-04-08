import './bootstrap';

import Alpine from 'alpinejs';
import Tooltip from '@ryangjchandler/alpine-tooltip';
import liveMatch from './live-match';
import lineupManager from './lineup';
import negotiationChat from './negotiation-chat';
import seasonSummary from './season-summary';
import squadSelection from './squad-selection';
import tournamentSummary from './tournament-summary';

Alpine.plugin(Tooltip);

Alpine.data('liveMatch', liveMatch);
Alpine.data('lineupManager', lineupManager);
Alpine.data('negotiationChat', negotiationChat);
Alpine.data('seasonSummary', seasonSummary);
Alpine.data('squadSelection', squadSelection);
Alpine.data('tournamentSummary', tournamentSummary);

window.Alpine = Alpine;

Alpine.start();

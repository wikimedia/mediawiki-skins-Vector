import { PORTALS, wrapPortlet } from './portal.stories.data';

export default {
	title: 'Portals'
};

export const portal = () => wrapPortlet( PORTALS.example );
export const navigationPortal = () => wrapPortlet( PORTALS.navigation );
export const toolbox = () => wrapPortlet( PORTALS.toolbox );
export const langlinks = () => wrapPortlet( PORTALS.langlinks );
export const otherProjects = () => wrapPortlet( PORTALS.otherProjects );

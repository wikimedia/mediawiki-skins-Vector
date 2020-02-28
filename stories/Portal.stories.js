import { PORTALS, wrapPortlet } from './Portal.stories.data';

export default {
	title: 'Portal'
};

export const portal = () => wrapPortlet( PORTALS.example );
export const navigationPortal = () => wrapPortlet( PORTALS.navigation );
export const toolbox = () => wrapPortlet( PORTALS.toolbox );
export const langlinks = () => wrapPortlet( PORTALS.langlinks );
export const otherProjects = () => wrapPortlet( PORTALS.otherProjects );

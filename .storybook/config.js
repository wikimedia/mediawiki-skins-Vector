import { configure } from '@storybook/html';
import './common.less';

// automatically import all files ending in *.stories.js
configure(require.context('../stories', true, /\.stories\.js$/), module);

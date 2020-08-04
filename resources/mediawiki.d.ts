interface MwApi {
	saveOption( name: string, value: unknown ): JQuery.Promise<any>;
}

type MwApiConstructor = new( options?: Object ) => MwApi;

interface MediaWiki {
	util: {
		/**
		 * Return a wrapper function that is debounced for the given duration.
		 *
		 * When it is first called, a timeout is scheduled. If before the timer
		 * is reached the wrapper is called again, it gets rescheduled for the
		 * same duration from now until it stops being called. The original function
		 * is called from the "tail" of such chain, with the last set of arguments.
		 *
		 * @since 1.34
		 * @param {number} delay Time in milliseconds
		 * @param {Function} callback
		 * @return {Function}
		 */
		debounce(delay: number, callback: Function): () => void;
	};
	Api: MwApiConstructor;
	config: {
		get( configKey: string|null ): string;
	},
	loader: {
		/**
		 * Execute a function after one or more modules are ready.
		 * 
		 * @param moduleName 
		 */
		using( moduleName: string|null ): JQuery.Promise<any>;
		
		/**
		 * Load a given resourceLoader module.
		 * 
		 * @param moduleName 
		 */
		 load( moduleName: string|null ): () => void;
		/**
		 * Get the loading state of the module. 
		 * On of 'registered', 'loaded', 'loading', 'ready', 'error', or 'missing'.
		 * 
		 * @param moduleName 
		 */
		getState( moduleName: string|null ): string; 
	}, 
	/**
	 * Loads the specified i18n message string. 
	 * Shortcut for `mw.message( key, parameters... ).text()`.
	 * 
	 * @param messageName i18n message name
	 */
	msg( messageName: string|null ): string;
}

declare const mw: MediaWiki;

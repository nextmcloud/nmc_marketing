/**
 * Find our login in the list of alternative logins
 * @return {string} the effective delegated login (with query params!), null if no delegated login is available
 */
function getDelegatedLogin() {
	try {
		// FIXME remove legacy way to access initialState, use typescript
		// and
		// import { loadState } from '@nextcloud/initial-state'
		// const val = loadState('myapp', 'user_preference')
		const logins = OCP.InitialState.loadState('core', 'alternativeLogins')
		const tlogin = logins.find((login) => login.name.toLowerCase().includes('telekom'))
		return (tlogin !== null) ? tlogin.href : null
	} catch (e) {
		return null
	}
}

/**
 *
 */
function hideLogins() {
    const wrapper = document.querySelector('.wrapper');
    if (!wrapper) return;

    Array.from(wrapper.children).forEach(child => {
        child.style.visibility = 'hidden';
    });
}

/**
 * This is the auto login delegate sequence for utag
 */
window.addEventListener('DOMContentLoaded', function() {
	const loginParams = new URLSearchParams(window.location.search)
	const isDefaultLoginPage = (window.location.pathname.endsWith('/login')
                             && !loginParams.has('direct', '1'))
	if (isDefaultLoginPage) {
		// only if I am on the default login page (and not ?direct=1 or guest)
		const delegatedLoginUrl = getDelegatedLogin()
		if (delegatedLoginUrl !== null) {
			// only if an alternate, delegated login is available
			hideLogins()

			// redirect to delegate login after consent selection
			window.addEventListener('consentChanged', function() {
				window.location.href = delegatedLoginUrl
			})

			// or redirect directly after page load
			// if utag lib is available and consent is already given
			if (typeof utag === 'object' // eslint-disable-line no-undef
                && typeof utag.gdpr === 'object' // eslint-disable-line no-undef
                && utag.gdpr.getConsentState() !== 0) { // eslint-disable-line no-undef
				window.location.href = delegatedLoginUrl
			}
		}
	}
})

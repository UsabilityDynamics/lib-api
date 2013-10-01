/**
 * Run Coverage Tests / Module
 *
 * @author potanin@UD
 */
module.exports = process.env.APP_COVERAGE ? require( './static/codex/lib-cov/api' ) : require( './scripts/api' );


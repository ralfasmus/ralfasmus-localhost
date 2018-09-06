
function logError(s) {
  if (logLevel > 0) {
    logError(s);
  }
}


function logWarn(s) {
  if (logLevel > 1) {
    console.warn(s);
  }
}

function logInfo(s) {
  if (logLevel > 2) {
    console.info(s);
  }
}

function logDebug(s) {
  if (logLevel > 3) {
    console.debug(s);
  }
}

function logTrace(s) {
  if (logLevel > 4) {
    console.trace(s);
  }
}
import React from 'react';
import * as jsxRuntime from 'react/jsx-runtime';
import * as ReactDOM from 'react-dom';
import * as client from 'react-dom/client';

export default React;

// React (instancia única compartida: react + react/jsx-runtime + react-dom).
export const {
    useCallback,
    useContext,
    useDebugValue,
    useDeferredValue,
    useEffect,
    useId,
    useImperativeHandle,
    useInsertionEffect,
    useLayoutEffect,
    useMemo,
    useReducer,
    useRef,
    useState,
    useSyncExternalStore,
    useTransition,
    use,
    Fragment,
    createElement,
    createContext,
    createRef,
    forwardRef,
    lazy,
    memo,
    isValidElement,
    startTransition,
    version,
    Children,
    cloneElement,
    Component,
    PureComponent,
    createFactory,
    act,
} = React;

// react/jsx-runtime.
export const { jsx, jsxs } = jsxRuntime;

// react-dom.
export const {
    createPortal,
    flushSync,
    preconnect,
    prefetchDNS,
    preinit,
    preinitModule,
    preload,
    preloadModule,
    requestFormReset,
    useFormState,
    useFormStatus,
} = ReactDOM;

// react-dom/client.
export const { createRoot, hydrateRoot } = client;
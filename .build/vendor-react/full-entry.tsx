import React from 'react';
import * as jsxRuntime from 'react/jsx-runtime';
import * as ReactDOM from 'react-dom';
import * as client from 'react-dom/client';
import * as compilerRuntime from 'react/compiler-runtime';

export default React;

// React Compiler: instancia única de `c` (useMemoCache) compartida con los
// bundles de tema que importan desde react/compiler-runtime.
export const { c } = compilerRuntime;

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
    useActionState,
    useOptimistic,
    cache,
    Fragment,
    StrictMode,
    Suspense,
    Profiler,
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
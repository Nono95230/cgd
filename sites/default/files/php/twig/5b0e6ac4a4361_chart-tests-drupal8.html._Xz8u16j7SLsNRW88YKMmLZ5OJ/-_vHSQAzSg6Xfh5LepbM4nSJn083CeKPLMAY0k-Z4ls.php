<?php

/* themes/custom/bootstrap_child/templates/chart-tests-drupal8.html.twig */
class __TwigTemplate_9ab6c1492eb6b832d42dac28d2cbf8b09633c58e6e23f6e0a1623b372e7c03d1 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $tags = array("if" => 2);
        $filters = array();
        $functions = array();

        try {
            $this->env->getExtension('Twig_Extension_Sandbox')->checkSecurity(
                array('if'),
                array(),
                array()
            );
        } catch (Twig_Sandbox_SecurityError $e) {
            $e->setSourceContext($this->getSourceContext());

            if ($e instanceof Twig_Sandbox_SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

        // line 1
        echo "<h2 id=\"test_d8_block_result_title\" class=\"block-title\">Résultats des tests Drupal 8</h2>
";
        // line 2
        if ($this->getAttribute(($context["data"] ?? null), "anytest", array())) {
            // line 3
            echo "\t<div id=\"test_d8-charts\">
\t\t<div id=\"charts\"></div>
\t</div>

";
        }
    }

    public function getTemplateName()
    {
        return "themes/custom/bootstrap_child/templates/chart-tests-drupal8.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  48 => 3,  46 => 2,  43 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("<h2 id=\"test_d8_block_result_title\" class=\"block-title\">Résultats des tests Drupal 8</h2>
{% if data.anytest %}
\t<div id=\"test_d8-charts\">
\t\t<div id=\"charts\"></div>
\t</div>

{% endif %}
", "themes/custom/bootstrap_child/templates/chart-tests-drupal8.html.twig", "C:\\wamp64\\www\\cgd\\themes\\custom\\bootstrap_child\\templates\\chart-tests-drupal8.html.twig");
    }
}
